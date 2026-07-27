<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Services/TmmSsoService.php';

use App\Services\TmmSsoService;

function tokenResponse(int $status, bool $success, string $message, ?array $data = null): never
{
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'errors' => $success ? [] : [['code' => 'invalid_grant']],
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        tokenResponse(405, false, 'Method not allowed.');
    }

    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        tokenResponse(422, false, 'A valid JSON request body is required.');
    }

    $client = TmmSsoService::validateClient($input, true);
    $code = trim((string) ($input['code'] ?? ''));
    $service = new TmmSsoService($db);
    $claims = $service->exchangeCode($code, $client['client_id'], $client['redirect_uri']);

    tokenResponse(200, true, 'Authorization code exchanged.', $claims);
} catch (InvalidArgumentException $exception) {
    tokenResponse(401, false, $exception->getMessage());
} catch (Throwable $exception) {
    error_log('TMM SSO token error: ' . $exception->getMessage());
    tokenResponse(500, false, 'The authorization service is temporarily unavailable.');
}
