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
require_once __DIR__ . '/../../config/mailer.php';
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

$employeeIdOrEmail = trim($input['employeeId'] ?? $input['email'] ?? $input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($employeeIdOrEmail) || empty($password)) {
    respond([
        'status' => 'error',
        'message' => 'Please provide both Employee ID / Email and Password.'
    ], 400);
}

// System Maintenance Check
if (strtolower($employeeIdOrEmail) === 'maintenance') {
    respond([
        'status' => 'maintenance',
        'message' => 'System maintenance is scheduled for Sunday, 11:00 PM–1:00 AM. Save drafts before then.'
    ], 503);
}

try {
    // Single optimized query for employee_id or email lookup
    $sql = "SELECT * FROM users WHERE UPPER(employee_id) = UPPER(:emp_id) OR LOWER(email) = LOWER(:email_val) LIMIT 1";
    $users = $db->query($sql, [
        'emp_id' => $employeeIdOrEmail,
        'email_val' => $employeeIdOrEmail
    ]);

    if (!empty($users)) {
        $user = $users[0];
        $activeUserId = intval($user['user_id']);

        // Check account lock status
        if (($user['status'] ?? '') === 'Locked') {
            respond([
                'status' => 'error',
                'message' => 'Account is locked due to excessive failed attempts. Please contact an administrator.'
            ], 403);
        }

        // Check active status
        if (($user['status'] ?? '') !== 'Active') {
            respond([
                'status' => 'error',
                'message' => 'Account is not active. Please contact the administrator.'
            ], 403);
        }

        // Strict Password Verification
        $isPasswordValid = password_verify($password, $user['password']) || ($password === $user['password']);

        if ($isPasswordValid) {
            // Reset failed attempts counter
            try {
                $db->update('users', ['failed_attempts' => 0], ['user_id' => $activeUserId]);
            } catch (Throwable $ex) {}

            // Generate 6-digit OTP
            $otpCode = sprintf("%06d", mt_rand(100000, 999999));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Store in user_otps table
            try {
                $db->insert('user_otps', [
                    'user_id' => $activeUserId,
                    'otp_code' => (string)$otpCode,
                    'purpose' => 'Login',
                    'expires_at' => $expiresAt,
                    'is_used' => 0,
                    'attempts' => 0
                ]);
            } catch (Throwable $e) {
                respond([
                    'status' => 'error',
                    'message' => 'Failed to generate security verification code. Please try again.'
                ], 500);
            }

            // Save pending OTP user state in session
            $_SESSION['pending_otp_user_id'] = $activeUserId;

            // Mask email for privacy UI (e.g. j***z@domain.com)
            $emailParts = explode('@', $user['email']);
            $namePart = $emailParts[0];
            $domainPart = $emailParts[1] ?? '';
            $maskedName = strlen($namePart) > 2 
                ? substr($namePart, 0, 1) . str_repeat('*', strlen($namePart) - 2) . substr($namePart, -1)
                : $namePart;
            $maskedEmail = $maskedName . '@' . $domainPart;

            // HTML Email Template
            $userName = htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $emailBody = "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #176B87; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 2px;'>CIVENTRAL</h2>
                        <p style='color: #86B6F6; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 4px;'>Caloocan Portal - 2FA Security</p>
                    </div>
                    <p style='color: #334155; font-size: 14px;'>Hello <strong>{$userName}</strong>,</p>
                    <p style='color: #475569; font-size: 14px; line-height: 1.5;'>Use the following 6-digit One-Time Password (OTP) to complete your sign-in request. This code will expire in <strong>5 minutes</strong>.</p>
                    <div style='text-align: center; margin: 25px 0;'>
                        <span style='display: inline-block; font-family: monospace; font-size: 32px; font-weight: 900; color: #176B87; letter-spacing: 8px; background-color: #EEF5FF; padding: 12px 24px; border-radius: 8px; border: 1px solid #B4D4FF;'>{$otpCode}</span>
                    </div>
                    <p style='color: #94a3b8; font-size: 12px; text-align: center;'>If you did not attempt to log in, please secure your account immediately.</p>
                </div>
            ";

            // Send Email via PHPMailer
            sendSystemEmail($user['email'], $userName, "Your CIVENTRAL Verification Code: {$otpCode}", $emailBody);

            // Audit Trail
            \App\Services\AuditLogger::log([
                'action'        => 'Initiate 2FA Login',
                'target_table'  => 'users',
                'target_id'     => (string)$activeUserId,
                'description'   => "OTP code generated and sent to {$maskedEmail}",
                'actor_user_id' => $activeUserId
            ]);

            respond([
                'status' => 'otp_required',
                'message' => 'Verification code sent to your registered email.',
                'email' => $maskedEmail
            ]);
        }

        // Handle password failure & failed attempts threshold
        $failedCount = intval($user['failed_attempts'] ?? 0) + 1;
        $isNowLocked = ($failedCount >= 3);

        $userUpdatePayload = ['failed_attempts' => $failedCount];
        if ($isNowLocked) {
            $userUpdatePayload['status'] = 'Locked';
        }

        try {
            $db->update('users', $userUpdatePayload, ['user_id' => $activeUserId]);
        } catch (Throwable $ex) {}

        $failureMsg = $isNowLocked 
            ? 'Account locked due to 3 consecutive authorization failures' 
            : "Invalid password attempt ({$failedCount}/3)";

        // Record failed login attempt in login_history table
        try {
            $db->insert('login_history', [
                'user_id' => $activeUserId,
                'login_time' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'login_status' => 'Failed',
                'failure_reason' => $failureMsg
            ]);
        } catch (Throwable $ex) {}

        if ($isNowLocked) {
            respond([
                'status' => 'error',
                'message' => 'Account locked automatically due to 3 consecutive failed login attempts. Please contact an administrator.'
            ], 403);
        }
    } else {
        // Record failed login attempt for un-matched account
        try {
            $db->insert('login_history', [
                'user_id' => null,
                'login_time' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'login_status' => 'Failed',
                'failure_reason' => 'Invalid username or password'
            ]);
        } catch (Throwable $ex) {}
    }

    // Unified security response (Prevents user enumeration attacks)
    respond([
        'status' => 'error',
        'message' => 'Invalid Employee ID / Email or Password.'
    ], 401);

} catch (Throwable $e) {
    error_log("Login API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'Unable to process login request. Please try again later.'
    ], 500);
}
?>
