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
$email = strtolower(trim($input['email'] ?? ''));
$purpose = trim($input['purpose'] ?? 'Registration');

if (!in_array($purpose, ['Registration', 'Login', 'Password Reset'])) {
    $purpose = 'Registration';
}

if (empty($otpCode) || strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
    respond([
        'status' => 'error',
        'message' => 'Please enter a valid 6-digit verification code.'
    ], 400);
}

try {
    // 1. Identify Citizen User by email or active pending session
    $citizenUserId = $_SESSION['pending_citizen_user_id'] ?? null;
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
            'message' => 'Citizen user account not found. Please register or check your email.'
        ], 404);
    }

    // 2. Fetch Latest Unused OTP for this citizen user & purpose
    $otps = $db->query(
        "SELECT * FROM citizen_otps 
         WHERE citizen_user_id = :cid 
           AND purpose = :purpose 
           AND is_used = 0 
         ORDER BY otp_id DESC LIMIT 1",
        [
            'cid' => $citizenUserId,
            'purpose' => $purpose
        ]
    );

    if (empty($otps)) {
        respond([
            'status' => 'error',
            'message' => 'No active verification code found. Please request a new code.'
        ], 400);
    }

    $otpRecord = $otps[0];
    $otpId = intval($otpRecord['otp_id']);

    // Check expiration (5-minute TTL)
    if (strtotime($otpRecord['expires_at']) < time()) {
        try {
            $db->exec("UPDATE citizen_otps SET is_used = 1 WHERE otp_id = :id", ['id' => $otpId]);
        } catch (Throwable $ex) {}

        respond([
            'status' => 'error',
            'message' => 'Verification code has expired. Please request a new code.'
        ], 400);
    }

    // Verify OTP Code
    if ($otpRecord['otp_code'] !== $otpCode) {
        respond([
            'status' => 'error',
            'message' => 'Invalid verification code. Please check your email and try again.'
        ], 400);
    }

    // 3. Mark OTP as verified & used
    try {
        $db->exec(
            "UPDATE citizen_otps SET is_used = 1, verified_at = :vtime WHERE otp_id = :id",
            [
                'vtime' => date('Y-m-d H:i:s'),
                'id' => $otpId
            ]
        );
    } catch (Throwable $ex) {}

    // 4. Update Citizen User Account Status
    $userUpdates = ['failed_attempts' => 0];
    if ($citizenUser['status'] === 'Pending' || $purpose === 'Registration') {
        $userUpdates['status'] = 'Active';
    }

    try {
        $db->update('citizen_users', $userUpdates, ['citizen_user_id' => $citizenUserId]);
    } catch (Throwable $ex) {}

    // Set active citizen session
    $_SESSION['citizen_user_id'] = $citizenUserId;
    $_SESSION['citizen_email'] = $citizenUser['email'];
    $_SESSION['citizen_name'] = trim(($citizenUser['first_name'] ?? '') . ' ' . ($citizenUser['last_name'] ?? ''));
    unset($_SESSION['pending_citizen_user_id']);

    // Record login history
    try {
        $db->insert('citizen_login_history', [
            'citizen_user_id' => $citizenUserId,
            'session_id' => null,
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'login_status' => 'Success',
            'failure_reason' => null
        ]);
    } catch (Throwable $logEx) {}

    respond([
        'status' => 'success',
        'message' => 'Email verification successful!',
        'citizen_user_id' => $citizenUserId,
        'email' => $citizenUser['email'],
        'purpose' => $purpose,
        'user' => [
            'citizen_user_id' => $citizenUserId,
            'first_name' => $citizenUser['first_name'],
            'last_name' => $citizenUser['last_name'],
            'email' => $citizenUser['email'],
            'status' => 'Active',
            'registry_completed' => !empty($citizenUser['registry_completed'])
        ]
    ]);

} catch (Throwable $e) {
    error_log("Citizen Verify OTP Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
