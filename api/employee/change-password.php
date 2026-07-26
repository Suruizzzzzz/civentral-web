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

try {
    // 2. Authentication Check
    $userId = $_SESSION['user_id'] ?? null;
    $employeeId = $_SESSION['employee_id'] ?? null;

    if (!$userId && !$employeeId) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session. Please sign in to update your password.'
        ], 401);
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?? $_POST;

    $currentPass = trim($data['current_password'] ?? '');
    $newPass = trim($data['new_password'] ?? '');

    if (empty($currentPass) || empty($newPass)) {
        respond([
            'status' => 'error',
            'message' => 'Both current password and new password are required.'
        ], 400);
    }

    // Fetch User Record
    $users = [];
    if ($userId) {
        $users = $db->select('users', ['user_id' => $userId]);
    } elseif ($employeeId) {
        $users = $db->select('users', ['employee_id' => $employeeId]);
    }

    if (empty($users)) {
        respond([
            'status' => 'error',
            'message' => 'User account record not found.'
        ], 404);
    }

    $user = $users[0];
    $activeUserId = intval($user['user_id']);

    // Check account status
    if (($user['status'] ?? '') !== 'Active') {
        respond([
            'status' => 'error',
            'message' => 'Account is not active. Unable to change password.'
        ], 403);
    }

    // 3. Password Verification
    $isPasswordValid = password_verify($currentPass, $user['password']) || ($currentPass === $user['password']);
    if (!$isPasswordValid) {
        // Audit log failed password change attempt
        try {
            $db->insert('audit_logs', [
                'actor_user_id' => $activeUserId,
                'action' => 'Change Password Failure',
                'target_table' => 'users',
                'target_id' => (string)$activeUserId,
                'description' => 'Failed password change attempt: Incorrect current password provided.',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'request_method' => 'POST',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/change-password.php',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'status' => 'Failed'
            ]);
        } catch (Throwable $auditEx) {}

        respond([
            'status' => 'error',
            'message' => 'Incorrect current password. Please try again.'
        ], 400);
    }

    // Prevent using identical new password
    if (password_verify($newPass, $user['password']) || $currentPass === $newPass) {
        respond([
            'status' => 'error',
            'message' => 'New password cannot be the same as your current password.'
        ], 400);
    }

    // 4. Password Policy Complexity Validation
    if (
        strlen($newPass) < 8 || 
        !preg_match('/[A-Z]/', $newPass) || 
        !preg_match('/[a-z]/', $newPass) || 
        !preg_match('/[0-9]/', $newPass) || 
        !preg_match('/[!@#$&*^%\-_+=?<>]/', $newPass)
    ) {
        respond([
            'status' => 'error',
            'message' => 'New password must be at least 8 characters long and contain uppercase, lowercase, numeric, and special characters.'
        ], 400);
    }

    // Hash new password using modern BCRYPT
    $hashedPassword = password_hash($newPass, PASSWORD_BCRYPT);

    // 5. Update Password & Reset First Login Flag
    $db->update('users', [
        'password' => $hashedPassword,
        'is_first_login' => 0,
        'password_changed_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ], ['user_id' => $activeUserId]);

    // 6. Non-blocking Audit Trail Log
    try {
        $db->insert('audit_logs', [
            'actor_user_id' => $activeUserId,
            'action' => 'Change Password',
            'target_table' => 'users',
            'target_id' => (string)$activeUserId,
            'description' => 'User successfully updated their account password.',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'request_method' => 'POST',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/change-password.php',
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'status' => 'Success'
        ]);
    } catch (Throwable $auditEx) {
        error_log("Audit log failed: " . $auditEx->getMessage());
    }

    respond([
        'status' => 'success',
        'message' => 'Your password has been updated successfully!'
    ]);

} catch (Throwable $e) {
    error_log("Change Password API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
