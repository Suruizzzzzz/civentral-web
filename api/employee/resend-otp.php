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

// 2. Pending Session Validation
if (empty($_SESSION['pending_otp_user_id'])) {
    respond([
        'status' => 'error',
        'message' => 'Session expired. Please sign in again.'
    ], 401);
}

$userId = intval($_SESSION['pending_otp_user_id']);

try {
    // Fetch User
    $users = $db->select('users', ['user_id' => $userId]);
    if (empty($users)) {
        respond([
            'status' => 'error',
            'message' => 'User account record not found.'
        ], 404);
    }

    $user = $users[0];

    // Check account status
    if (($user['status'] ?? '') !== 'Active') {
        respond([
            'status' => 'error',
            'message' => 'Account is not active. Unable to send verification code.'
        ], 403);
    }

    // 3. 60-Second Cooldown Rate Limiting Check
    $latestOtps = $db->query(
        "SELECT created_at FROM user_otps WHERE user_id = :uid AND purpose = 'Login' ORDER BY otp_id DESC LIMIT 1",
        ['uid' => $userId]
    );

    if (!empty($latestOtps)) {
        $lastSentTime = strtotime($latestOtps[0]['created_at']);
        $secondsPassed = time() - $lastSentTime;
        if ($secondsPassed < 60) {
            $waitTime = 60 - $secondsPassed;
            respond([
                'status' => 'error',
                'message' => "Please wait {$waitTime} seconds before requesting another verification code."
            ], 429);
        }
    }

    // Invalidate previous unused OTPs for this user
    try {
        $db->update('user_otps', ['is_used' => 1], ['user_id' => $userId, 'purpose' => 'Login', 'is_used' => 0]);
    } catch (Throwable $ex) {}

    // Generate new 6-digit OTP
    $otpCode = sprintf("%06d", mt_rand(100000, 999999));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Insert new OTP record
    $db->insert('user_otps', [
        'user_id' => $userId,
        'otp_code' => (string)$otpCode,
        'purpose' => 'Login',
        'expires_at' => $expiresAt,
        'is_used' => 0,
        'attempts' => 0
    ]);

    // Mask Email
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
                <p style='color: #86B6F6; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 4px;'>Caloocan Portal - Resend Verification Code</p>
            </div>
            <p style='color: #334155; font-size: 14px;'>Hello <strong>{$userName}</strong>,</p>
            <p style='color: #475569; font-size: 14px; line-height: 1.5;'>Your new 6-digit One-Time Password (OTP) is below. This code will expire in <strong>5 minutes</strong>.</p>
            <div style='text-align: center; margin: 25px 0;'>
                <span style='display: inline-block; font-family: monospace; font-size: 32px; font-weight: 900; color: #176B87; letter-spacing: 8px; background-color: #EEF5FF; padding: 12px 24px; border-radius: 8px; border: 1px solid #B4D4FF;'>{$otpCode}</span>
            </div>
            <p style='color: #94a3b8; font-size: 12px; text-align: center;'>If you did not request this verification code, please ignore this email.</p>
        </div>
    ";

    sendSystemEmail($user['email'], $userName, "Your New CIVENTRAL Verification Code: {$otpCode}", $emailBody);

    // Audit Trail
    \App\Services\AuditLogger::log([
        'action'        => 'Resend 2FA OTP',
        'target_table'  => 'users',
        'target_id'     => (string)$userId,
        'description'   => "Resent 2FA verification code to {$maskedEmail}",
        'actor_user_id' => $userId
    ]);

    respond([
        'status' => 'success',
        'message' => 'A new verification code has been sent to your email.',
        'email' => $maskedEmail
    ]);

} catch (Throwable $e) {
    error_log("Resend OTP API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'Failed to resend verification code. Please try again later.'
    ], 500);
}
?>
