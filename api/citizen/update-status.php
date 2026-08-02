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

// Authentication Check
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    respond([
        'status' => 'error',
        'message' => 'Unauthorized session. Please sign in to update citizen account status.'
    ], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$citId = trim($input['citizen_id'] ?? $input['id'] ?? '');
$dbId = filter_var($input['db_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$action = trim($input['action'] ?? '');
$reason = trim($input['reason'] ?? '');

if (empty($action)) {
    respond(['status' => 'error', 'message' => 'Action is required.'], 400);
}

try {
    $user = null;
    if ($dbId) {
        $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $dbId]);
        if (!empty($users)) $user = $users[0];
    } elseif (!empty($citId)) {
        // Try searching by exact ID string or numeric ID
        $cleanId = preg_replace('/[^\d]/', '', $citId);
        if ($cleanId) {
            $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => intval($cleanId)]);
            if (!empty($users)) $user = $users[0];
        }
    }

    if (!$user) {
        respond(['status' => 'error', 'message' => 'Citizen user record not found.'], 404);
    }

    $targetUserId = intval($user['citizen_user_id']);
    $newStatus = $user['status'];
    $failedAttempts = intval($user['failed_attempts'] ?? 0);

    if (in_array($action, ['Activate', 'Unlock', 'Restore'])) {
        $newStatus = 'Active';
        $failedAttempts = 0;
    } elseif ($action === 'Deactivate') {
        $newStatus = 'Inactive';
    } elseif ($action === 'Lock') {
        $newStatus = 'Locked';
    } elseif ($action === 'Suspend') {
        $newStatus = 'Suspended';
    }

    $db->update('citizen_users', [
        'status' => $newStatus,
        'failed_attempts' => $failedAttempts,
        'updated_at' => date('Y-m-d H:i:s')
    ], ['citizen_user_id' => $targetUserId]);

    respond([
        'status' => 'success',
        'message' => "Security status updated to {$newStatus}.",
        'new_status' => $newStatus
    ]);

} catch (Throwable $e) {
    error_log("Update Citizen Status Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred.'
    ], 500);
}
?>
