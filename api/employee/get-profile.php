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

header('Access-Control-Allow-Methods: GET, OPTIONS');
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

if ($method !== 'GET') {
    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);
}

try {
    // 2. Strict Authentication Check (NO hardcoded fallback)
    $userId = $_SESSION['user_id'] ?? null;
    $employeeId = $_SESSION['employee_id'] ?? null;

    if (!$userId && !$employeeId) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session. Please sign in to view your profile.'
        ], 401);
    }

    // 3. Single Optimized SQL JOIN Query
    $sql = "SELECT u.*, 
                   r.role_name, r.role_prefix, r.is_global_access,
                   p.position_name, p.department_id,
                   d.department_name, d.department_code
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN positions p ON u.position_id = p.position_id
            LEFT JOIN departments d ON p.department_id = d.department_id
            WHERE u.user_id = :user_id OR (u.employee_id = :emp_id AND :emp_id_check IS NOT NULL)
            LIMIT 1";

    $users = $db->query($sql, [
        'user_id' => $userId ?: 0,
        'emp_id' => $employeeId ?: '',
        'emp_id_check' => $employeeId
    ]);

    if (empty($users)) {
        respond([
            'status' => 'error',
            'message' => 'User account record not found.'
        ], 404);
    }

    $user = $users[0];
    $activeUserId = intval($user['user_id']);

    // Total Logins Count from login_history
    $totalLogins = 0;
    try {
        $loginsCount = $db->query("SELECT COUNT(*) AS total FROM login_history WHERE user_id = :user_id AND login_status = 'Success'", [
            'user_id' => $activeUserId
        ]);
        $totalLogins = intval($loginsCount[0]['total'] ?? 0);
    } catch (Throwable $logEx) {}

    $middleNamePart = !empty($user['middle_name']) ? trim($user['middle_name']) . ' ' : '';
    $fullName = trim(($user['first_name'] ?? '') . ' ' . $middleNamePart . ($user['last_name'] ?? ''));
    $initials = strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? 'S', 0, 1));
    $lastLoginFormatted = !empty($user['last_login']) 
        ? date('F j, Y, g:i A', strtotime($user['last_login'])) 
        : 'N/A';

    respond([
        'status' => 'success',
        'data' => [
            'user_id' => $activeUserId,
            'employee_id' => $user['employee_id'],
            'first_name' => $user['first_name'] ?? '',
            'middle_name' => $user['middle_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'full_name' => $fullName,
            'initials' => $initials,
            'email' => $user['email'] ?? '',
            'mobile_number' => $user['mobile_number'] ?? '',
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'] ?? 'Staff',
            'role_prefix' => $user['role_prefix'] ?? 'STF',
            'position_id' => $user['position_id'],
            'position_name' => $user['position_name'] ?? 'Staff Member',
            'department_id' => $user['department_id'],
            'department_name' => $user['department_name'] ?? 'General Office',
            'department_code' => $user['department_code'] ?? 'GEN',
            'status' => $user['status'] ?? 'Active',
            'last_login' => $lastLoginFormatted,
            'total_logins' => $totalLogins,
            'profile_picture' => $user['profile_picture'] ?? 'default-avatar.png'
        ]
    ]);

} catch (Throwable $e) {
    error_log("Get Profile API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
