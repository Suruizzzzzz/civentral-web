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

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Preflight Handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Services/NotificationService.php';

// Response Helper
function respond(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    // 2. Authentication Check
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session. Please sign in.'
        ], 401);
    }

    $notifService = new \App\Services\NotificationService();

    // 3. API Actions
    $action = $_GET['action'] ?? '';

    if ($method === 'GET') {
        if ($action === 'unread_count') {
            $count = $notifService->getUnreadCount((int)$userId);
            respond([
                'status' => 'success',
                'count' => $count
            ]);
        } 
        
        if ($action === 'list') {
            $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 25;
            $list = $notifService->getForUser((int)$userId, $limit);
            respond([
                'status' => 'success',
                'notifications' => $list
            ]);
        }

        respond([
            'status' => 'error',
            'message' => 'Invalid GET action specified.'
        ], 400);

    } elseif ($method === 'POST') {
        // Parse input body for POST requests
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if ($action === 'mark_read') {
            $notifId = isset($input['notification_id']) ? (int)$input['notification_id'] : 0;
            if ($notifId <= 0) {
                respond([
                    'status' => 'error',
                    'message' => 'Invalid notification_id parameter.'
                ], 400);
            }

            $success = $notifService->markAsRead($notifId, (int)$userId);
            if ($success) {
                respond([
                    'status' => 'success',
                    'message' => 'Notification marked as read.'
                ]);
            } else {
                respond([
                    'status' => 'error',
                    'message' => 'Failed to mark notification as read, or record not found.'
                ], 500);
            }
        }

        if ($action === 'mark_all_read') {
            $success = $notifService->markAllAsRead((int)$userId);
            if ($success) {
                respond([
                    'status' => 'success',
                    'message' => 'All unread notifications marked as read.'
                ]);
            } else {
                respond([
                    'status' => 'error',
                    'message' => 'Failed to mark notifications as read.'
                ], 500);
            }
        }

        respond([
            'status' => 'error',
            'message' => 'Invalid POST action specified.'
        ], 400);
    }

    respond([
        'status' => 'error',
        'message' => 'Unsupported request method.'
    ], 405);

} catch (Throwable $e) {
    respond([
        'status' => 'error',
        'message' => 'Server Error: ' . $e->getMessage()
    ], 500);
}
