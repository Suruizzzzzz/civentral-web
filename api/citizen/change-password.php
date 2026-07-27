<?php
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

$citizenUserId = intval($input['citizen_user_id'] ?? 0);
$email = trim($input['email'] ?? '');
$currentPassword = trim($input['current_password'] ?? '');
$newPassword = trim($input['new_password'] ?? '');

if (empty($currentPassword) || empty($newPassword)) {
    respond([
        'status' => 'error',
        'message' => 'Please provide both your current password and your new password.'
    ], 400);
}

if ($citizenUserId <= 0 && empty($email)) {
    respond([
        'status' => 'error',
        'message' => 'Invalid user credentials.'
    ], 400);
}

// Password validation (Strong password enforcement matching mobile app criteria)
// - Minimum 8 characters
// - Has uppercase letter
// - Has lowercase letter
// - Has digit
// - Has special character
$hasUppercase = preg_match('/[A-Z]/', $newPassword);
$hasLowercase = preg_match('/[a-z]/', $newPassword);
$hasNumber = preg_match('/[0-9]/', $newPassword);
$hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword);
$isLongEnough = strlen($newPassword) >= 8;

if (!$isLongEnough || !$hasUppercase || !$hasLowercase || !$hasNumber || !$hasSpecial) {
    respond([
        'status' => 'error',
        'message' => 'New password must be strong (at least 8 characters, and contain uppercase, lowercase, number, and special character).'
    ], 400);
}

try {
    // Find citizen user by ID or Email
    if ($citizenUserId > 0) {
        $sql = "SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1";
        $users = $db->query($sql, ['id' => $citizenUserId]);
    } else {
        $sql = "SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1";
        $users = $db->query($sql, ['email' => $email]);
    }

    if (empty($users)) {
        respond([
            'status' => 'error',
            'message' => 'Citizen account not found.'
        ], 404);
    }

    $user = $users[0];
    $userId = intval($user['citizen_user_id']);

    // Check if account status is locked or inactive
    if ($user['status'] === 'Locked' || $user['status'] === 'Inactive') {
        respond([
            'status' => 'error',
            'message' => 'This account is locked or inactive. Please contact support.'
        ], 403);
    }

    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        respond([
            'status' => 'error',
            'message' => 'Current password entered is incorrect.'
        ], 400);
    }

    // Hashing new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password in database
    $db->update('citizen_users', [
        'password' => $hashedPassword
    ], [
        'citizen_user_id' => $userId
    ]);

    respond([
        'status' => 'success',
        'message' => 'Password has been updated successfully.'
    ]);

} catch (Exception $e) {
    respond([
        'status' => 'error',
        'message' => 'An error occurred on the server: ' . $e->getMessage()
    ], 500);
}
