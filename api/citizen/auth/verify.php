<?php
/**
 * CIVENTRAL CORE - Citizen Session Token Verification Endpoint
 *
 * Standalone, isolated endpoint allowing external services (such as Education)
 * to validate opaque Citizen session tokens issued by CIVENTRAL Core.
 *
 * Endpoint: POST /api/citizen/auth/verify.php
 * Authentication: Authorization: Bearer <citizen_session_token>
 */

ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Dynamic CORS Policy matching CIVENTRAL Core standards
$allowedOrigins = [
    'http://localhost',
    'http://localhost:80',
    'http://localhost:3000',
    'http://localhost:8081',
    'http://127.0.0.1',
    'http://127.0.0.1:80',
    'http://127.0.0.1:8081'
];

if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (in_array($origin, $allowedOrigins) || preg_match('/^http:\/\/(localhost|127\.0\.0\.1|192\.168\.\d+\.\d+|10\.\d+\.\d+\.\d+)(:\d+)?$/', $origin)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
    }
} else {
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle HTTP Preflight OPTIONS Request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/**
 * Standardized API Response Helper
 */
function respond(array $payload, int $statusCode = 200): void {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Safely extract Authorization header across various server configurations
 */
function getAuthorizationHeader(): ?string {
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                return trim($value);
            }
        }
    }
    return null;
}

// HTTP Method Validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
}

// Bootstrap Core Database Configuration
require_once __DIR__ . '/../../../config/database.php';

try {
    // 1. Extract Authorization Header
    $authHeader = getAuthorizationHeader();
    if (empty($authHeader)) {
        respond(['status' => 'error', 'message' => 'Invalid or expired citizen session.'], 401);
    }

    // 2. Validate Bearer Scheme Format
    if (!preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) {
        respond(['status' => 'error', 'message' => 'Invalid or expired citizen session.'], 401);
    }

    $rawToken = $matches[1];
    if (empty($rawToken)) {
        respond(['status' => 'error', 'message' => 'Invalid or expired citizen session.'], 401);
    }

    // 3. Hash Raw Token
    $tokenHash = hash('sha256', $rawToken);

    // 4. Query citizen_sessions table for active, unrevoked, unexpired session
    $sqlSession = "SELECT session_id, citizen_user_id, expires_at, is_revoked 
                   FROM citizen_sessions 
                   WHERE refresh_token_hash = :hash 
                     AND is_revoked = 0 
                     AND expires_at > NOW() 
                   LIMIT 1";
    $sessions = $db->query($sqlSession, ['hash' => $tokenHash]);

    if (empty($sessions)) {
        respond(['status' => 'error', 'message' => 'Invalid or expired citizen session.'], 401);
    }

    $session = $sessions[0];
    $citizenUserId = intval($session['citizen_user_id']);

    // 5. Query citizen_users to verify account existence and Active status
    $sqlUser = "SELECT citizen_user_id, email, status, deleted_at 
                FROM citizen_users 
                WHERE citizen_user_id = :cid 
                LIMIT 1";
    $users = $db->query($sqlUser, ['cid' => $citizenUserId]);

    if (empty($users)) {
        respond(['status' => 'error', 'message' => 'Invalid or expired citizen session.'], 401);
    }

    $user = $users[0];

    // Enforce Core active account rules: status must be Active and deleted_at must be NULL
    if (($user['status'] ?? '') !== 'Active' || !empty($user['deleted_at'])) {
        respond(['status' => 'error', 'message' => 'Invalid or expired citizen session.'], 401);
    }

    // 6. Return minimal authoritative identity
    // Note: Read-only operation. Does NOT alter last_active_at, expires_at, or token hash.
    respond([
        'status' => 'success',
        'data' => [
            'citizen_user_id' => intval($user['citizen_user_id']),
            'email' => $user['email'],
            'account_status' => $user['status'],
            'session_id' => intval($session['session_id']),
            'expires_at' => $session['expires_at']
        ]
    ], 200);

} catch (Throwable $e) {
    error_log("Citizen Token Verify Error: " . $e->getMessage());
    respond(['status' => 'error', 'message' => 'An internal database server error occurred.'], 500);
}
