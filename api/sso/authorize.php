<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Services/TmmSsoService.php';

use App\Services\TmmSsoService;

function authorizationError(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Authorization failed</title>';
    echo '<body style="font-family:Arial,sans-serif;padding:40px;color:#172033">';
    echo '<h1>Unable to open Transport &amp; Mobility Management</h1>';
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        authorizationError(405, 'Method not allowed.');
    }

    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$userId) {
        $returnTo = rawurlencode((string) ($_SERVER['REQUEST_URI'] ?? '/api/sso/authorize.php'));
        header('Location: /login.php?return_to=' . $returnTo, true, 302);
        exit;
    }

    $state = trim((string) ($_GET['state'] ?? ''));
    if ($state === '' || strlen($state) > 200) {
        authorizationError(422, 'The authorization state is missing or invalid.');
    }

    $client = TmmSsoService::validateClient($_GET, false);
    $service = new TmmSsoService($db);
    $code = $service->issueCode((int) $userId, $client['client_id'], $client['redirect_uri']);
    $separator = str_contains($client['redirect_uri'], '?') ? '&' : '?';

    header(
        'Location: ' . $client['redirect_uri'] . $separator
        . http_build_query(['code' => $code, 'state' => $state], '', '&', PHP_QUERY_RFC3986),
        true,
        302,
    );
    exit;
} catch (InvalidArgumentException $exception) {
    authorizationError(403, $exception->getMessage());
} catch (Throwable $exception) {
    error_log('TMM SSO authorize error: ' . $exception->getMessage());
    authorizationError(500, 'The authorization service is temporarily unavailable.');
}
