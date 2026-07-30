<?php
// Prevent session lock issues during long DB queries
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// 1. Dynamic CORS Configuration
$allowedOrigins = [
    'http://localhost',
    'http://localhost:80',
    'http://localhost:3000',
    'http://127.0.0.1',
    'http://127.0.0.1:80'
];

if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (in_array($origin, $allowedOrigins) || preg_match('/^http:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
    }
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Preflight Handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Services/AuditLogger.php';

// Response Helper
function respond(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$otpCode = trim($input['otp'] ?? $input['otp_code'] ?? '');

// 2. Pending Session Validation
if (empty($_SESSION['pending_otp_user_id'])) {
    respond([
        'status' => 'error',
        'message' => 'Session expired. Please sign in again.'
    ], 401);
}

if (empty($otpCode) || strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
    respond([
        'status' => 'error',
        'message' => 'Please enter a valid 6-digit verification code.'
    ], 400);
}

$userId = intval($_SESSION['pending_otp_user_id']);

try {
    // Select matching unused OTP record for Login
    $otps = $db->query(
        "SELECT * FROM user_otps WHERE user_id = :user_id AND purpose = 'Login' AND is_used = 0 ORDER BY otp_id DESC LIMIT 1",
        ['user_id' => $userId]
    );

    if (empty($otps)) {
        respond([
            'status' => 'error',
            'message' => 'No active verification code found. Please request a new code.'
        ], 400);
    }

    $otpRecord = $otps[0];
    $otpId = intval($otpRecord['otp_id']);
    $currentAttempts = intval($otpRecord['attempts'] ?? 0) + 1;

    // Check if OTP code has expired
    if (strtotime($otpRecord['expires_at']) < time()) {
        try {
            $db->update('user_otps', ['is_used' => 1], ['otp_id' => $otpId]);
        } catch (Throwable $ex) {}

        respond([
            'status' => 'error',
            'message' => 'Verification code has expired. Please request a new code.'
        ], 400);
    }

    // Check maximum failed attempts limit (5 attempts max)
    if ($currentAttempts > 5) {
        try {
            $db->update('user_otps', ['is_used' => 1], ['otp_id' => $otpId]);
        } catch (Throwable $ex) {}

        unset($_SESSION['pending_otp_user_id']);

        respond([
            'status' => 'error',
            'message' => 'Too many failed verification attempts. Verification code invalidated. Please sign in again.'
        ], 403);
    }

    // Update attempts counter
    try {
        $db->update('user_otps', ['attempts' => $currentAttempts], ['otp_id' => $otpId]);
    } catch (Throwable $ex) {}

    // Verify OTP Code matching
    if ($otpRecord['otp_code'] !== $otpCode) {
        $remaining = 5 - $currentAttempts;
        $failMessage = ($remaining > 0)
            ? "Invalid verification code. You have {$remaining} attempt(s) remaining."
            : "Maximum verification attempts exceeded. Please sign in again.";

        respond([
            'status' => 'error',
            'message' => $failMessage
        ], 400);
    }

    // Mark OTP as verified and used
    try {
        $db->update('user_otps', [
            'is_used' => 1,
            'verified_at' => date('Y-m-d H:i:s')
        ], ['otp_id' => $otpId]);
    } catch (Throwable $ex) {}

    // Fetch User details to complete active session setup
    $users = $db->select('users', ['user_id' => $userId]);
    if (empty($users)) {
        respond([
            'status' => 'error',
            'message' => 'User account not found.'
        ], 404);
    }

    $user = $users[0];

    if (($user['status'] ?? '') !== 'Active') {
        respond([
            'status' => 'error',
            'message' => 'Account is not active. Unable to complete login.'
        ], 403);
    }

    // Set active session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['employee_id'] = $user['employee_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['role_id'] = $user['role_id'];

    unset($_SESSION['pending_otp_user_id']);

    // Record active session in DB and login history
    try {
        $db->update('users', [
            'last_login' => date('Y-m-d H:i:s'),
            'failed_attempts' => 0
        ], ['user_id' => $user['user_id']]);

        // 1. Insert into user_sessions
        $sessionData = [
            'user_id' => $user['user_id'],
            'login_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+8 hours'))
        ];
        $sessionId = $db->insert('user_sessions', $sessionData);

        if ($sessionId) {
            $_SESSION['session_id'] = $sessionId;
        }

        // 2. Create login history record
        $loginData = [
            'user_id' => $user['user_id'],
            'session_id' => $sessionId,
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'login_status' => 'Success'
        ];
        $loginId = $db->insert('login_history', $loginData);
        if ($loginId) {
            $_SESSION['login_id'] = $loginId;
        }

        // 3. Create Audit Log
        \App\Services\AuditLogger::log([
            'action'        => '2FA Verification Success',
            'target_table'  => 'users',
            'target_id'     => (string)$user['user_id'],
            'description'   => "User completed 2FA verification and signed in.",
            'actor_user_id' => $user['user_id']
        ]);
    } catch (Throwable $logEx) {}

    respond([
        'status' => 'success',
        'message' => 'OTP verified successfully! Redirecting...',
        'user' => [
            'user_id' => $user['user_id'],
            'employee_id' => $user['employee_id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role_id' => $user['role_id'],
            'is_first_login' => !empty($user['is_first_login'])
        ]
    ]);

} catch (Throwable $e) {
    error_log("Verify OTP API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'Failed to verify OTP code. Please try again later.'
    ], 500);
}
?>
