<?php
// Prevent session lock issues during long DB queries
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Dynamic CORS Policy (Allows Mobile Apps, Expo Dev Servers, Web Apps)
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

$email = strtolower(trim($input['email'] ?? ''));
$purpose = trim($input['purpose'] ?? 'Registration');

if (!in_array($purpose, ['Registration', 'Login', 'Password Reset'])) {
    $purpose = 'Registration';
}

try {
    // 1. Identify Citizen User
    $citizenUserId = $_SESSION['pending_citizen_user_id'] ?? $_SESSION['citizen_user_id'] ?? null;
    $citizenUser = null;

    if (!empty($email)) {
        $users = $db->query("SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
        if (!empty($users)) {
            $citizenUser = $users[0];
            $citizenUserId = intval($citizenUser['citizen_user_id']);
        }
    } elseif ($citizenUserId) {
        $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $citizenUserId]);
        if (!empty($users)) {
            $citizenUser = $users[0];
        }
    }

    if (!$citizenUser || !$citizenUserId) {
        respond([
            'status' => 'error',
            'message' => 'Citizen user account not found.'
        ], 404);
    }

    // 2. 60-Second Cooldown Check
    $latestOtps = $db->query(
        "SELECT created_at FROM citizen_otps WHERE citizen_user_id = :cid AND purpose = :purpose ORDER BY otp_id DESC LIMIT 1",
        ['cid' => $citizenUserId, 'purpose' => $purpose]
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

    // 3. Invalidate Previous Unused OTPs
    try {
        $db->exec(
            "UPDATE citizen_otps SET is_used = 1 WHERE citizen_user_id = :cid AND purpose = :purpose AND is_used = 0",
            ['cid' => $citizenUserId, 'purpose' => $purpose]
        );
    } catch (Throwable $ex) {}

    // 4. Generate New 6-Digit OTP
    $otpCode = sprintf("%06d", mt_rand(100000, 999999));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $db->insert('citizen_otps', [
        'citizen_user_id' => $citizenUserId,
        'otp_code' => (string)$otpCode,
        'purpose' => $purpose,
        'expires_at' => $expiresAt,
        'is_used' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // Mask Email
    $emailParts = explode('@', $citizenUser['email']);
    $namePart = $emailParts[0];
    $domainPart = $emailParts[1] ?? '';
    $maskedName = strlen($namePart) > 2 
        ? substr($namePart, 0, 1) . str_repeat('*', strlen($namePart) - 2) . substr($namePart, -1)
        : $namePart;
    $maskedEmail = $maskedName . '@' . $domainPart;

    // HTML Email Template
    $userName = htmlspecialchars(trim(($citizenUser['first_name'] ?? '') . ' ' . ($citizenUser['last_name'] ?? '')));
    $emailBody = "
        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #176B87; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 2px;'>CIVENTRAL</h2>
                <p style='color: #86B6F6; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 4px;'>Citizen Portal - Email Verification Code</p>
            </div>
            <p style='color: #334155; font-size: 14px;'>Hello <strong>{$userName}</strong>,</p>
            <p style='color: #475569; font-size: 14px; line-height: 1.5;'>Your 6-digit One-Time Password (OTP) for account verification is below. This code will expire in <strong>5 minutes</strong>.</p>
            <div style='text-align: center; margin: 25px 0;'>
                <span style='display: inline-block; font-family: monospace; font-size: 32px; font-weight: 900; color: #176B87; letter-spacing: 8px; background-color: #EEF5FF; padding: 12px 24px; border-radius: 8px; border: 1px solid #B4D4FF;'>{$otpCode}</span>
            </div>
            <p style='color: #94a3b8; font-size: 12px; text-align: center;'>If you did not request this verification code, please ignore this email.</p>
        </div>
    ";

    sendSystemEmail($citizenUser['email'], $userName, "CIVENTRAL Verification Code: {$otpCode}", $emailBody);

    respond([
        'status' => 'success',
        'message' => 'Verification code sent successfully.',
        'email' => $maskedEmail,
        'purpose' => $purpose
    ]);

} catch (Throwable $e) {
    error_log("Citizen Resend OTP Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'Failed to send verification code. Please try again later.'
    ], 500);
}
?>
