<?php
declare(strict_types=1);

use App\Services\DriverInvitations;
use App\Services\DriverInvitationError;

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Allow: GET, POST, DELETE');
$root = dirname(__DIR__, 3);
require_once $root . '/src/Services/DriverInvitations.php';
require_once $root . '/config/driver-invitations.php';
try {
    if (driverInvitationSetting('CIVENTRAL_DRIVER_INVITATIONS_ENABLED') !== 'true') {
        throw new DriverInvitationError(503, 'integration_disabled', 'Driver invitations are not enabled.');
    }
    $expected = driverInvitationSetting('CIVENTRAL_TRANSPORT_INVITATION_SERVICE_KEY');
    $key = (string)($_SERVER['HTTP_X_INTERNAL_SERVICE_KEY'] ?? '');
    if ($expected === '') throw new DriverInvitationError(503, 'integration_unconfigured', 'Invitation service authentication is not configured.');
    if ($key === '' || !hash_equals($expected, $key)) throw new DriverInvitationError(401, 'service_authentication_required', 'Service authentication required.');
    // Load existing backend dependencies only after dedicated service authentication.
    require_once $root . '/config/database.php';
    $db = Database::getInstance();
    require_once $root . '/src/Services/AuditLogger.php';
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $config = driverInvitationConfig($method);
    $input = $_GET;
    if (in_array($method, ['POST','DELETE'], true)) {
        $raw = file_get_contents('php://input', false, null, 0, 32769);
        if (!is_string($raw) || strlen($raw) > 32768) throw new DriverInvitationError(413, 'request_too_large', 'Request is too large.');
        try { $input = json_decode($raw, true, 32, JSON_THROW_ON_ERROR); }
        catch (JsonException) { throw new DriverInvitationError(400, 'invalid_json', 'Send a JSON object.'); }
        if (!is_array($input) || !str_starts_with(ltrim($raw), '{')) throw new DriverInvitationError(400, 'invalid_json', 'Send a JSON object.');
    }
    [$status, $body] = (new DriverInvitations($db->getPdo(), $config))->handle($method, $key, $input);
} catch (Throwable $exception) {
    $known = $exception instanceof DriverInvitationError;
    $status = $known ? $exception->httpStatus : 500;
    $body = ['status' => 'error', 'code' => $known ? $exception->errorCode : 'invitation_request_failed',
        'message' => $known ? $exception->getMessage() : 'Unable to process the invitation request.'];
    if (class_exists(\App\Services\AuditLogger::class)) {
        try { driverInvitationAudit('driver_invitation_request', ['reason' => $body['code']], $known ? 'rejected' : 'failed'); }
        catch (Throwable) { error_log('Driver invitation audit unavailable.'); }
    } else error_log('Driver invitation request rejected: '.$body['code']);
}
http_response_code($status);
echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
