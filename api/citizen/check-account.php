<?php
// Prevent session lock issues
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
$identifier = strtolower(trim($input['email'] ?? $input['identifier'] ?? $input['mobile_number'] ?? ''));

if (empty($identifier)) {
    respond([
        'status' => 'error',
        'message' => 'Please enter an email address or phone number.'
    ], 400);
}

try {
    $sql = "SELECT citizen_user_id, status FROM citizen_users WHERE LOWER(email) = LOWER(:val) OR mobile_number = :val LIMIT 1";
    $users = $db->query($sql, ['val' => $identifier]);

    if (!empty($users)) {
        respond([
            'status' => 'exists',
            'exists' => true,
            'user_status' => $users[0]['status'],
            'message' => 'Account exists.'
        ]);
    } else {
        respond([
            'status' => 'not_found',
            'exists' => false,
            'message' => 'Account does not exist.'
        ]);
    }
} catch (Throwable $e) {
    error_log("Check Account Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'Database error during account check.'
    ], 500);
}
?>
