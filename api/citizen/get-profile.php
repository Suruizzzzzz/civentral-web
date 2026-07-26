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

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

try {
    // Determine Citizen User Identity from Session or Query Params
    $citizenUserId = $_SESSION['citizen_user_id'] ?? filter_var($_GET['citizen_user_id'] ?? $_POST['citizen_user_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $email = strtolower(trim($_GET['email'] ?? $_POST['email'] ?? ''));

    $user = null;
    if ($citizenUserId) {
        $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $citizenUserId]);
        if (!empty($users)) {
            $user = $users[0];
        }
    } elseif (!empty($email)) {
        $users = $db->query("SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
        if (!empty($users)) {
            $user = $users[0];
        }
    }

    if (!$user) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session or citizen user not found. Please sign in.'
        ], 401);
    }

    $activeUserId = intval($user['citizen_user_id']);

    // Construct Full Name
    $middlePart = !empty($user['middle_name']) ? trim($user['middle_name']) . ' ' : '';
    $suffixPart = !empty($user['suffix']) ? ' ' . trim($user['suffix']) : '';
    $fullName = trim(($user['first_name'] ?? '') . ' ' . $middlePart . ($user['last_name'] ?? '') . $suffixPart);
    $initials = strtoupper(substr($user['first_name'] ?? 'C', 0, 1) . substr($user['last_name'] ?? 'U', 0, 1));
    $createdDate = !empty($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'N/A';
    $lastLoginFormatted = !empty($user['last_login']) ? date('F j, Y, g:i A', strtotime($user['last_login'])) : 'N/A';

    respond([
        'status' => 'success',
        'data' => [
            'citizen_user_id' => $activeUserId,
            'first_name' => $user['first_name'] ?? '',
            'middle_name' => $user['middle_name'] ?? '',
            'has_no_middle_name' => !empty($user['has_no_middle_name']),
            'last_name' => $user['last_name'] ?? '',
            'suffix' => $user['suffix'] ?? '',
            'full_name' => $fullName,
            'initials' => $initials,
            'email' => $user['email'] ?? '',
            'mobile_number' => $user['mobile_number'] ?? '',
            'status' => $user['status'] ?? 'Pending',
            'registry_completed' => !empty($user['registry_completed']),
            'biometric_enabled' => !empty($user['biometric_enabled']),
            'last_login' => $lastLoginFormatted,
            'member_since' => $createdDate,
            'created_at' => $user['created_at']
        ]
    ]);

} catch (Throwable $e) {
    error_log("Get Citizen Profile Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
