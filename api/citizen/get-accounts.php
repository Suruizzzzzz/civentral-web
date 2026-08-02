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

header('Access-Control-Allow-Methods: GET, OPTIONS');
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

try {
    // Authentication Check
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session. Please sign in to view citizen accounts.'
        ], 401);
    }

    $users = $db->query("SELECT * FROM citizen_users ORDER BY created_at DESC");

    $formattedUsers = [];
    foreach ($users as $user) {
        $middlePart = !empty($user['middle_name']) ? trim($user['middle_name']) . ' ' : '';
        $suffixPart = !empty($user['suffix']) ? ' ' . trim($user['suffix']) : '';
        $fullName = trim(($user['first_name'] ?? '') . ' ' . $middlePart . ($user['last_name'] ?? '') . $suffixPart);
        
        $createdYear = !empty($user['created_at']) ? date('Y', strtotime($user['created_at'])) : date('Y');
        $idStr = 'CIT-' . $createdYear . '-' . str_pad($user['citizen_user_id'], 4, '0', STR_PAD_LEFT);
        
        $status = $user['status'] ?? 'Active';
        $failedAttempts = intval($user['failed_attempts'] ?? 0);
        $flagged = ($status === 'Locked' || $status === 'Suspended' || $failedAttempts > 0);
        
        $violations = '0 failed login attempts';
        if ($status === 'Locked') {
            $violations = 'Locked by administrator';
        } else if ($status === 'Suspended') {
            $violations = 'Flagged: Security Investigation Hold';
        } else if ($status === 'Inactive') {
            $violations = 'Deactivated by administrator';
        } else if ($failedAttempts > 0) {
            $violations = "{$failedAttempts} failed login attempt(s)";
        }

        // Timeline entry built dynamically
        $timeline = [
            [
                'action' => "Account Status: {$status}",
                'admin' => "System",
                'date' => !empty($user['updated_at']) ? date('F j, Y', strtotime($user['updated_at'])) : date('F j, Y'),
                'reason' => "Current security status evaluated as {$status}."
            ],
            [
                'action' => "Registration Completed",
                'admin' => "System",
                'date' => !empty($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : date('F j, Y'),
                'reason' => "Citizen registered account."
            ]
        ];

        $formattedUsers[] = [
            'id' => $idStr,
            'db_id' => $user['citizen_user_id'],
            'name' => $fullName,
            'email' => $user['email'] ?? '',
            'status' => $status,
            'violations' => $violations,
            'flagged' => $flagged,
            'timeline' => $timeline
        ];
    }

    respond([
        'status' => 'success',
        'data' => $formattedUsers
    ]);

} catch (Throwable $e) {
    error_log("Get Citizen Accounts Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
