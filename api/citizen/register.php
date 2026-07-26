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

$firstName = trim($input['first_name'] ?? '');
$middleName = trim($input['middle_name'] ?? '');
$hasNoMiddleName = !empty($input['has_no_middle_name']) ? 1 : 0;
$lastName = trim($input['last_name'] ?? '');
$suffix = trim($input['suffix'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$mobileNumber = trim($input['mobile_number'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
    respond([
        'status' => 'error',
        'message' => 'First Name, Last Name, Email, and Password are required.'
    ], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond([
        'status' => 'error',
        'message' => 'Please enter a valid email address.'
    ], 400);
}

if (strlen($password) < 8) {
    respond([
        'status' => 'error',
        'message' => 'Password must be at least 8 characters long.'
    ], 400);
}

try {
    // Check if email already registered
    $existingEmail = $db->query("SELECT citizen_user_id, status FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
    
    if (!empty($existingEmail)) {
        $existing = $existingEmail[0];
        if ($existing['status'] === 'Pending') {
            // Re-use pending account & resend OTP
            $citizenUserId = intval($existing['citizen_user_id']);
        } else {
            respond([
                'status' => 'error',
                'message' => 'Email address is already registered. Please sign in.'
            ], 400);
        }
    } else {
        // Hash Password
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);

        // Sanitize Mobile Number
        $mobileNumber = preg_replace('/[^\d+]/', '', $mobileNumber) ?: null;

        $insertPayload = [
            'first_name' => $firstName,
            'middle_name' => $hasNoMiddleName ? null : ($middleName ?: null),
            'has_no_middle_name' => $hasNoMiddleName,
            'last_name' => $lastName,
            'suffix' => $suffix ?: null,
            'email' => $email,
            'mobile_number' => $mobileNumber,
            'password' => $hashedPass,
            'status' => 'Pending',
            'registry_completed' => 0,
            'failed_attempts' => 0,
            'biometric_enabled' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $citizenUserId = $db->insert('citizen_users', $insertPayload);
    }

    // Invalidate prior unused OTPs for Registration
    try {
        $db->exec(
            "UPDATE citizen_otps SET is_used = 1 WHERE citizen_user_id = :cid AND purpose = 'Registration' AND is_used = 0",
            ['cid' => $citizenUserId]
        );
    } catch (Throwable $invEx) {}

    // Generate 6-digit OTP Code
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

    // Save pending state in session
    $_SESSION['pending_citizen_user_id'] = $citizenUserId;

    // Mask Email
    $emailParts = explode('@', $email);
    $namePart = $emailParts[0];
    $domainPart = $emailParts[1] ?? '';
    $maskedName = strlen($namePart) > 2 
        ? substr($namePart, 0, 1) . str_repeat('*', strlen($namePart) - 2) . substr($namePart, -1)
        : $namePart;
    $maskedEmail = $maskedName . '@' . $domainPart;

    // HTML Email Template
    $userName = htmlspecialchars("{$firstName} {$lastName}");
    $emailBody = "
        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #176B87; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 2px;'>CIVENTRAL</h2>
                <p style='color: #86B6F6; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 4px;'>Citizen Registration - Verification Code</p>
            </div>
            <p style='color: #334155; font-size: 14px;'>Hello <strong>{$userName}</strong>,</p>
            <p style='color: #475569; font-size: 14px; line-height: 1.5;'>Thank you for registering with CIVENTRAL. Please use the following 6-digit One-Time Password (OTP) to complete your email verification. This code will expire in <strong>5 minutes</strong>.</p>
            <div style='text-align: center; margin: 25px 0;'>
                <span style='display: inline-block; font-family: monospace; font-size: 32px; font-weight: 900; color: #176B87; letter-spacing: 8px; background-color: #EEF5FF; padding: 12px 24px; border-radius: 8px; border: 1px solid #B4D4FF;'>{$otpCode}</span>
            </div>
            <p style='color: #94a3b8; font-size: 12px; text-align: center;'>If you did not register for a CIVENTRAL account, please ignore this message.</p>
        </div>
    ";

    sendSystemEmail($email, "{$firstName} {$lastName}", "CIVENTRAL Verification Code: {$otpCode}", $emailBody);

    respond([
        'status' => 'otp_required',
        'message' => 'Account created! Verification code sent to your email.',
        'citizen_user_id' => $citizenUserId,
        'email' => $maskedEmail
    ], 201);

} catch (Throwable $e) {
    error_log("Citizen Register Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
