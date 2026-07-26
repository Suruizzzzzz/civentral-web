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

$method = $_SERVER['REQUEST_METHOD'];

try {
    // 2. Authentication Check
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session. Please sign in to view or manage audit logs.'
        ], 401);
    }

    // 3. Authorization Check (Must be Super Administrator / Authorized Role)
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

    if (!$isAuthorized) {
        respond([
            'status' => 'error',
            'message' => 'Forbidden. You do not have administrative privileges to view system audit logs.'
        ], 403);
    }

    // 4. GET Handler (Fetch system audit logs)
    if ($method === 'GET') {
        $sqlLogs = "SELECT al.*, 
                           u.user_id, u.first_name, u.last_name, u.email, u.profile_picture,
                           r.role_name, r.role_prefix,
                           d.department_id, d.department_name, d.department_code,
                           m.module_id, m.module_name
                    FROM audit_logs al
                    LEFT JOIN users u ON al.actor_user_id = u.user_id
                    LEFT JOIN roles r ON u.role_id = r.role_id
                    LEFT JOIN departments d ON al.department_id = d.department_id
                    LEFT JOIN modules m ON al.module_id = m.module_id
                    ORDER BY al.created_at DESC
                    LIMIT 1000";
        $rawLogs = $db->query($sqlLogs) ?: [];

        $logs = [];
        foreach ($rawLogs as $row) {
            $logs[] = [
                'audit_id' => $row['audit_id'],
                'actor_user_id' => $row['actor_user_id'],
                'session_id' => $row['session_id'],
                'department_id' => $row['department_id'],
                'module_id' => $row['module_id'],
                'resource_id' => $row['resource_id'],
                'action' => $row['action'],
                'target_table' => $row['target_table'],
                'target_id' => $row['target_id'],
                'description' => $row['description'],
                'ip_address' => $row['ip_address'],
                'request_method' => $row['request_method'],
                'request_uri' => $row['request_uri'],
                'browser' => $row['browser'],
                'operating_system' => $row['operating_system'],
                'status' => $row['status'],
                'context_json' => $row['context_json'],
                'created_at' => $row['created_at'],
                'users' => $row['user_id'] ? [
                    'user_id' => $row['user_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'profile_picture' => $row['profile_picture'],
                    'roles' => [
                        'role_name' => $row['role_name'],
                        'role_prefix' => $row['role_prefix']
                    ]
                ] : null,
                'departments' => $row['department_id'] ? [
                    'department_id' => $row['department_id'],
                    'department_name' => $row['department_name'],
                    'department_code' => $row['department_code']
                ] : null,
                'modules' => $row['module_id'] ? [
                    'module_id' => $row['module_id'],
                    'module_name' => $row['module_name']
                ] : null
            ];
        }

        $departments = $db->query("SELECT department_id, department_code, department_name FROM departments ORDER BY department_name ASC");
        $modules = $db->query("SELECT module_id, module_name FROM modules ORDER BY module_name ASC");

        respond([
            'status' => 'success',
            'data' => $logs ?: [],
            'departments' => $departments ?: [],
            'modules' => $modules ?: []
        ]);
    }

    // 5. POST Handler (Record audit event)
    if ($method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true) ?? $_POST;

        // Prevent actor impersonation: actor_user_id MUST be the authenticated user ID
        $actorUserId = $userId;
        $action = trim($data['action'] ?? 'View');
        $description = trim($data['description'] ?? '');
        $departmentId = filter_var($data['department_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $moduleId = filter_var($data['module_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $targetTable = !empty($data['target_table']) ? trim($data['target_table']) : null;
        $targetId = !empty($data['target_id']) ? strval($data['target_id']) : null;
        $status = in_array($data['status'] ?? '', ['Success', 'Failed']) ? $data['status'] : 'Success';
        $contextJson = !empty($data['context_json']) ? $data['context_json'] : null;

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'POST';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/api/employee/audit-logs.php';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $insertPayload = [
            'actor_user_id' => $actorUserId,
            'session_id' => $_SESSION['session_id'] ?? null,
            'department_id' => $departmentId,
            'module_id' => $moduleId,
            'action' => $action,
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'description' => $description,
            'ip_address' => $ipAddress,
            'request_method' => $requestMethod,
            'request_uri' => $requestUri,
            'browser' => $userAgent,
            'operating_system' => 'WebClient',
            'status' => $status,
            'context_json' => $contextJson ? json_encode($contextJson) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $newId = $db->insert('audit_logs', $insertPayload);

        respond([
            'status' => 'success',
            'message' => 'Audit log entry created successfully.',
            'audit_id' => $newId
        ]);
    }

    // Method Not Allowed
    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Audit Logs API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
