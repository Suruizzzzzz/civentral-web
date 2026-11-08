<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed'
    ]);
    exit;
}

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../src/Services/JwtService.php';

$authHeader = null;
if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (is_array($headers)) {
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }
    }
}

if (empty($authHeader)) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized - Missing Token'
    ]);
    exit;
}

if (!preg_match('/^Bearer\s+(.+)$/i', trim($authHeader), $matches)) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized - Malformed Bearer Header'
    ]);
    exit;
}

$token = trim($matches[1]);

try {
    $jwtService = new \App\Services\JwtService();
    $decoded = $jwtService->verifyToken($token);

    if (!$decoded || !is_array($decoded)) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized - Invalid or Expired Token'
        ]);
        exit;
    }

    if (isset($decoded['iss']) && $decoded['iss'] !== 'civentral-superadmin') {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized - Invalid Token Issuer'
        ]);
        exit;
    }

    $userId = $decoded['user_id'] ?? $decoded['sub'] ?? null;
    if (!$userId) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized - Invalid Token Payload'
        ]);
        exit;
    }

    /** @var \Database $db */
    global $db;

    $sql = "SELECT 
                u.user_id,
                u.employee_id,
                u.role_id,
                u.position_id,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.email,
                u.mobile_number,
                u.profile_picture,
                u.last_login,
                u.status,
                r.role_name,
                r.role_prefix,
                r.is_global_access,
                r.is_superadmin,
                p.position_name,
                COALESCE(p.department_id, r.department_id) AS department_id,
                d.department_name,
                d.department_name AS department
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN positions p ON u.position_id = p.position_id
            LEFT JOIN departments d ON d.department_id = COALESCE(p.department_id, r.department_id)
            WHERE u.user_id = :user_id
            LIMIT 1";

    $users = $db->query($sql, ['user_id' => $userId]);

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

    $roleName = $user['role_name'] ?? 'guest';

    $firstName = trim((string) ($user['first_name'] ?? ''));
    $middleName = trim((string) ($user['middle_name'] ?? ''));
    $lastName = trim((string) ($user['last_name'] ?? ''));

    $midPart = !empty($middleName) ? $middleName . ' ' : '';
    $fullName = trim($firstName . ' ' . $midPart . $lastName);
    if (empty($fullName)) {
        $fullName = (string) $roleName;
    }

    $initialFirst = !empty($firstName) ? substr($firstName, 0, 1) : (!empty($roleName) ? substr($roleName, 0, 1) : 'U');
    $initialLast = !empty($lastName) ? substr($lastName, 0, 1) : (!empty($roleName) ? substr($roleName, -1, 1) : 'S');
    $initials = strtoupper($initialFirst . $initialLast);

    $lastLoginFormatted = !empty($user['last_login']) 
        ? date('F j, Y, g:i A', strtotime((string) $user['last_login'])) 
        : 'Active Now';

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => [
            'user_id' => (int) $user['user_id'],
            'employee_id' => $user['employee_id'] !== null ? (string) $user['employee_id'] : null,
            'first_name' => $firstName,
            'middle_name' => !empty($middleName) ? $middleName : null,
            'last_name' => $lastName,
            'full_name' => $fullName,
            'initials' => $initials,
            'email' => $user['email'] !== null ? (string) $user['email'] : null,
            'mobile_number' => $user['mobile_number'] !== null ? (string) $user['mobile_number'] : null,
            'role_id' => $user['role_id'] !== null ? (int) $user['role_id'] : null,
            'role' => (string) $roleName,
            'role_name' => (string) $roleName,
            'role_prefix' => $user['role_prefix'] !== null ? (string) $user['role_prefix'] : null,
            'position_id' => isset($user['position_id']) && $user['position_id'] !== null ? (int) $user['position_id'] : null,
            'position_name' => $user['position_name'] !== null ? (string) $user['position_name'] : null,
            'department_id' => $user['department_id'] !== null ? (int) $user['department_id'] : null,
            'department' => $user['department'] !== null ? (string) $user['department'] : null,
            'department_name' => $user['department_name'] !== null ? (string) $user['department_name'] : ($user['department'] !== null ? (string) $user['department'] : null),
            'profile_picture' => !empty($user['profile_picture']) ? (string) $user['profile_picture'] : 'default-avatar.png',
            'last_login' => $lastLoginFormatted,
            'is_global_access' => isset($user['is_global_access']) ? (int) $user['is_global_access'] : 0,
            'is_superadmin' => isset($user['is_superadmin']) ? (int) $user['is_superadmin'] : 0,
            'status' => (string) ($user['status'] ?? 'Active')
        ]
    ]);
} catch (\Throwable $e) {
    error_log("Token Verification Endpoint Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An internal database server error occurred.'
    ]);
    exit;
}
