<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? '';

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed'
    ]);
    exit;
}

$sessionCookieName = session_name();

if (empty($_COOKIE[$sessionCookieName]) || !is_string($_COOKIE[$sessionCookieName])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized - Authentication Required'
    ]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start(['read_and_close' => true]);
}

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../src/Services/JwtService.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized - Authentication Required'
    ]);
    exit;
}

try {
    /** @var \Database $db */
    global $db;

    $users = $db->query(
        "SELECT user_id, employee_id, role_id, status FROM users WHERE user_id = :user_id LIMIT 1",
        ['user_id' => $userId]
    );

    if (empty($users)) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized - User Not Found'
        ]);
        exit;
    }

    $user = $users[0];

    if (strcasecmp((string) ($user['status'] ?? ''), 'Active') !== 0) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Forbidden - Account Inactive'
        ]);
        exit;
    }

    $jwtService = new \App\Services\JwtService();
    $accessToken = $jwtService->issueToken([
        'user_id' => (int) $user['user_id'],
        'employee_id' => $user['employee_id'] !== null ? (string) $user['employee_id'] : null,
        'role_id' => $user['role_id'] !== null ? (int) $user['role_id'] : null
    ]);

    if (!$accessToken) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unable to issue authentication token.'
        ]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'access_token' => $accessToken,
        'token_type' => 'Bearer',
        'expires_in' => (int) (getenv('JWT_EXPIRY') ?: 28800)
    ]);
} catch (\Throwable $e) {
    error_log("Token Issuance Endpoint Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to issue authentication token.'
    ]);
    exit;
}
