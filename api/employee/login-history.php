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

// Response Helper
function respond(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    // 2. Authentication Check
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session. Please sign in to view login history.'
        ], 401);
    }

    // 3. Authorization Check
    $currentUserRoles = $db->select('roles', ['role_id' => $_SESSION['role_id'] ?? 0]);
    $isAuthorized = false;

    if (!empty($currentUserRoles)) {
        $userRole = $currentUserRoles[0];
        if (
            !empty($userRole['is_global_access']) || 
            strtoupper($userRole['role_prefix'] ?? '') === 'SA' || 
            strtoupper($userRole['role_prefix'] ?? '') === 'SADM' ||
            !empty($userRole['is_system_role'])
        ) {
            $isAuthorized = true;
        }
    }

    if ($method === 'GET') {
        // If global admin, fetch all login histories; otherwise, limit to current user's login history
        if ($isAuthorized) {
            $sqlLogs = "SELECT lh.*, 
                               u.user_id, u.first_name, u.last_name, u.email, u.employee_id, u.status AS user_status,
                               r.role_id, r.role_name, r.role_prefix,
                               p.position_id, p.position_name,
                               d.department_id, d.department_name, d.department_code
                        FROM login_history lh
                        LEFT JOIN users u ON lh.user_id = u.user_id
                        LEFT JOIN roles r ON u.role_id = r.role_id
                        LEFT JOIN positions p ON u.position_id = p.position_id
                        LEFT JOIN departments d ON p.department_id = d.department_id
                        ORDER BY lh.login_time DESC
                        LIMIT 1000";
            $rawLogs = $db->query($sqlLogs) ?: [];
        } else {
            $sqlLogs = "SELECT lh.*, 
                               u.user_id, u.first_name, u.last_name, u.email, u.employee_id, u.status AS user_status,
                               r.role_id, r.role_name, r.role_prefix,
                               p.position_id, p.position_name,
                               d.department_id, d.department_name, d.department_code
                        FROM login_history lh
                        LEFT JOIN users u ON lh.user_id = u.user_id
                        LEFT JOIN roles r ON u.role_id = r.role_id
                        LEFT JOIN positions p ON u.position_id = p.position_id
                        LEFT JOIN departments d ON p.department_id = d.department_id
                        WHERE lh.user_id = :user_id
                        ORDER BY lh.login_time DESC
                        LIMIT 100";
            $rawLogs = $db->query($sqlLogs, ['user_id' => $userId]) ?: [];
        }

        $logs = [];
        foreach ($rawLogs as $row) {
            $logs[] = [
                'login_id' => $row['login_id'],
                'user_id' => $row['user_id'],
                'session_id' => $row['session_id'],
                'login_time' => $row['login_time'],
                'logout_time' => $row['logout_time'],
                'ip_address' => $row['ip_address'],
                'device_info' => $row['device_info'],
                'login_status' => $row['login_status'],
                'browser' => $row['browser'],
                'operating_system' => $row['operating_system'],
                'failure_reason' => $row['failure_reason'],
                'users' => $row['user_id'] ? [
                    'user_id' => $row['user_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'employee_id' => $row['employee_id'],
                    'status' => $row['user_status'],
                    'roles' => [
                        'role_id' => $row['role_id'],
                        'role_name' => $row['role_name'],
                        'role_prefix' => $row['role_prefix']
                    ],
                    'positions' => [
                        'position_id' => $row['position_id'],
                        'position_name' => $row['position_name'],
                        'departments' => [
                            'department_id' => $row['department_id'],
                            'department_name' => $row['department_name'],
                            'department_code' => $row['department_code']
                        ]
                    ]
                ] : null
            ];
        }

        $departments = $db->query("SELECT department_id, department_code, department_name FROM departments ORDER BY department_name ASC") ?: [];
        $lockedUsers = $isAuthorized ? ($db->query("SELECT user_id, status FROM users WHERE status = 'Locked'") ?: []) : [];

        // Dynamic security metrics calculation
        $successfulCount = 0;
        $failedCount = 0;
        $activeCount = 0;

        foreach ($logs as $log) {
            $status = $log['login_status'] ?? '';
            $logoutTime = $log['logout_time'] ?? null;

            if ($status === 'Success') {
                $successfulCount++;
                if (empty($logoutTime)) {
                    $activeCount++;
                }
            } elseif ($status === 'Failed') {
                $failedCount++;
            }
        }

        $lockCount = count($lockedUsers);

        respond([
            'status' => 'success',
            'data' => $logs,
            'departments' => $departments,
            'metrics' => [
                'successfulCount' => $successfulCount,
                'failedCount' => $failedCount,
                'activeCount' => $activeCount,
                'lockCount' => $lockCount
            ]
        ]);
    }

    if ($method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true) ?? $_POST;

        $targetUserId = filter_var($data['user_id'] ?? $userId, FILTER_VALIDATE_INT) ?: $userId;
        $sessionId = filter_var($data['session_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $loginStatus = in_array($data['status'] ?? $data['login_status'] ?? '', ['Success', 'Failed']) ? ($data['status'] ?? $data['login_status']) : 'Success';
        $failureReason = !empty($data['failure_reason']) ? trim($data['failure_reason']) : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $browser = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $insertPayload = [
            'user_id' => $targetUserId,
            'session_id' => $sessionId,
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $ipAddress,
            'browser' => $browser,
            'login_status' => $loginStatus,
            'failure_reason' => $failureReason
        ];

        $newId = $db->insert('login_history', $insertPayload);

        respond([
            'status' => 'success',
            'message' => 'Login history record created successfully.',
            'login_id' => $newId
        ], 201);
    }

    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Login History API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
