<?php
// Prevent session lock issues during long DB queries
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Dynamic CORS Policy
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

// Preflight Handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/mailer.php';

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

$emailOrMobile = strtolower(trim($input['email'] ?? $input['username'] ?? $input['mobile_number'] ?? ''));
$password = trim($input['password'] ?? '');

if (empty($emailOrMobile) || empty($password)) {
    respond([
        'status' => 'error',
        'message' => 'Please provide both Email / Mobile Number and Password.'
    ], 400);
}

try {
    // Single Query Lookup
    $sql = "SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email_val) OR mobile_number = :mobile_val LIMIT 1";
    $users = $db->query($sql, [
        'email_val' => $emailOrMobile,
        'mobile_val' => $emailOrMobile
    ]);

    if (!empty($users)) {
        $user = $users[0];
        $citizenUserId = intval($user['citizen_user_id']);

        if ($user['status'] === 'Locked') {
            respond([
                'status' => 'error',
                'message' => 'Account is locked due to excessive failed attempts. Please reset your password or contact support.'
            ], 403);
        }

        if ($user['status'] === 'Inactive' || $user['status'] === 'Archived') {
            respond([
                'status' => 'error',
                'message' => 'Account is inactive. Please contact support.'
            ], 403);
        }

        // Verify Password
        if (password_verify($password, $user['password'])) {
            // Reset failed attempts
            try {
                $db->update('citizen_users', ['failed_attempts' => 0], ['citizen_user_id' => $citizenUserId]);
            } catch (Throwable $ex) {}

            // If Status is Pending (unverified email) -> Require OTP verification
            if ($user['status'] === 'Pending') {
                $_SESSION['pending_citizen_user_id'] = $citizenUserId;

                // Generate & Dispatch OTP
                $otpCode = sprintf("%06d", mt_rand(100000, 999999));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                $db->insert('citizen_otps', [
                    'citizen_user_id' => $citizenUserId,
                    'otp_code' => (string)$otpCode,
                    'purpose' => 'Registration',
                    'expires_at' => $expiresAt,
                    'is_used' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Mask Email
                $emailParts = explode('@', $user['email']);
                $namePart = $emailParts[0];
                $domainPart = $emailParts[1] ?? '';
                $maskedName = strlen($namePart) > 2 
                    ? substr($namePart, 0, 1) . str_repeat('*', strlen($namePart) - 2) . substr($namePart, -1)
                    : $namePart;
                $maskedEmail = $maskedName . '@' . $domainPart;

                // Send Email
                $userName = htmlspecialchars("{$user['first_name']} {$user['last_name']}");
                $emailBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <h2 style='color: #176B87; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 2px;'>CIVENTRAL</h2>
                            <p style='color: #86B6F6; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 4px;'>Citizen Verification Required</p>
                        </div>
                        <p style='color: #334155; font-size: 14px;'>Hello <strong>{$userName}</strong>,</p>
                        <p style='color: #475569; font-size: 14px; line-height: 1.5;'>Please complete your email verification using the 6-digit code below. This code will expire in <strong>5 minutes</strong>.</p>
                        <div style='text-align: center; margin: 25px 0;'>
                            <span style='display: inline-block; font-family: monospace; font-size: 32px; font-weight: 900; color: #176B87; letter-spacing: 8px; background-color: #EEF5FF; padding: 12px 24px; border-radius: 8px; border: 1px solid #B4D4FF;'>{$otpCode}</span>
                        </div>
                    </div>
                ";

                sendSystemEmail($user['email'], "{$user['first_name']} {$user['last_name']}", "CIVENTRAL Verification Code: {$otpCode}", $emailBody);

                respond([
                    'status' => 'otp_required',
                    'message' => 'Please verify your email to complete login.',
                    'email' => $maskedEmail
                ]);
            }

            // Set Active Session
            $_SESSION['citizen_user_id'] = $citizenUserId;
            $_SESSION['citizen_email'] = $user['email'];
            $_SESSION['citizen_name'] = trim("{$user['first_name']} {$user['last_name']}");

            // Update Last Login
            try {
                $db->update('citizen_users', ['last_login' => date('Y-m-d H:i:s')], ['citizen_user_id' => $citizenUserId]);
                
                // Record Login History
                $db->insert('citizen_login_history', [
                    'citizen_user_id' => $citizenUserId,
                    'session_id' => null,
                    'login_time' => date('Y-m-d H:i:s'),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    'login_status' => 'Success',
                    'failure_reason' => null
                ]);
            } catch (Throwable $ex) {}

            respond([
                'status' => 'success',
                'message' => 'Login successful!',
                'user' => [
                    'citizen_user_id' => $citizenUserId,
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'status' => $user['status'],
                    'registry_completed' => !empty($user['registry_completed'])
                ]
            ]);
        }

        // Handle Password Failure Threshold (Lock after 5 failed attempts)
        $failedCount = intval($user['failed_attempts'] ?? 0) + 1;
        $isNowLocked = ($failedCount >= 5);

        $userUpdates = ['failed_attempts' => $failedCount];
        if ($isNowLocked) {
            $userUpdates['status'] = 'Locked';
        }

        try {
            $db->update('citizen_users', $userUpdates, ['citizen_user_id' => $citizenUserId]);
            
            $db->insert('citizen_login_history', [
                'citizen_user_id' => $citizenUserId,
                'session_id' => null,
                'login_time' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'login_status' => 'Failed',
                'failure_reason' => $isNowLocked ? 'Account locked due to 5 failed attempts' : 'Incorrect password'
            ]);
        } catch (Throwable $ex) {}
    } else {
        try {
            $db->insert('citizen_login_history', [
                'citizen_user_id' => null,
                'session_id' => null,
                'login_time' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'login_status' => 'Failed',
                'failure_reason' => 'Invalid email or password'
            ]);
        } catch (Throwable $ex) {}
    }

    respond([
        'status' => 'error',
        'message' => 'Invalid Email/Mobile Number or Password.'
    ], 401);

} catch (Throwable $e) {
    error_log("Citizen Login Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'Unable to process login. Please try again later.'
    ], 500);
}
?>
