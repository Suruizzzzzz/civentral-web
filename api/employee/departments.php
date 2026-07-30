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

header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Preflight Handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Services/AuditLogger.php';

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

    // Fetch user granted actions for Department Management resource
    $userPermRows = !empty($_SESSION['role_id']) ? ($db->query("
        SELECT DISTINCT UPPER(a.action_name) AS action_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.permission_id
        JOIN actions a ON p.action_id = a.action_id
        JOIN resources res ON p.resource_id = res.resource_id
        WHERE rp.role_id = :rid AND (LOWER(res.resource_name) LIKE '%department%')
    ", ['rid' => $_SESSION['role_id']]) ?: []) : [];

    $userGrantedActions = [];
    foreach ($userPermRows as $pr) {
        if (!empty($pr['action_name'])) $userGrantedActions[] = strtoupper($pr['action_name']);
    }

    $currentUserRoles = $db->select('roles', ['role_id' => $_SESSION['role_id'] ?? 0]);
    $isSuperAdmin = false;
    if (!empty($currentUserRoles)) {
        $uRole = $currentUserRoles[0];
        $rolePrefix = strtoupper($uRole['role_prefix'] ?? '');
        $roleName = strtolower($uRole['role_name'] ?? '');
        if ((!empty($uRole['is_superadmin']) && intval($uRole['is_superadmin']) === 1) || !empty($uRole['is_global_access']) || in_array($rolePrefix, ['SA', 'SADM']) || $roleName === 'super administrator' || $roleName === 'superadmin') {
            $isSuperAdmin = true;
        }
    }

    $canCreateDept = $isSuperAdmin || in_array('CREATE', $userGrantedActions);
    $canEditDept   = $isSuperAdmin || in_array('EDIT', $userGrantedActions);
    $canDeleteDept = $isSuperAdmin || in_array('DELETE', $userGrantedActions);

    if ($method === 'GET') {
        $sqlDepts = "SELECT d.*, u.user_id, u.first_name, u.last_name, u.email
                     FROM departments d
                     LEFT JOIN users u ON d.department_head_user_id = u.user_id
                     ORDER BY d.department_id ASC";
        $deptsRaw = $db->query($sqlDepts);
        
        $departments = [];
        foreach ($deptsRaw as $row) {
            $departments[] = [
                'department_id' => $row['department_id'],
                'department_code' => $row['department_code'],
                'department_name' => $row['department_name'],
                'department_head_user_id' => $row['department_head_user_id'],
                'description' => $row['description'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'users' => $row['user_id'] ? [
                    'user_id' => $row['user_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email']
                ] : null
            ];
        }

        $users = $db->query("SELECT user_id, first_name, last_name, email FROM users WHERE status = 'Active' ORDER BY last_name ASC");

        respond([
            'status' => 'success',
            'data' => $departments ?: [],
            'users' => $users ?: [],
            'current_user' => [
                'user_id' => (int)$userId,
                'is_superadmin' => $isSuperAdmin,
                'granted_actions' => $userGrantedActions
            ]
        ]);
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?? [];

    if ($method === 'POST') {
        if (!$canCreateDept) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to create departments.'
            ], 403);
        }
        // Create Department
        $name = trim($data['department_name'] ?? '');
        $code = strtoupper(trim($data['department_code'] ?? ''));
        $desc = trim($data['description'] ?? '');
        $status = in_array($data['status'] ?? '', ['Active', 'Inactive']) ? $data['status'] : 'Active';
        $headUserId = filter_var($data['department_head_user_id'] ?? null, FILTER_VALIDATE_INT) ?: null;

        if (empty($name) || empty($code)) {
            respond([
                'status' => 'error',
                'message' => 'Department Name and Department Code are required.'
            ], 400);
        }

        // Duplicate code/name check
        $existing = $db->query("SELECT department_id FROM departments WHERE department_code = :code OR LOWER(department_name) = LOWER(:name)", [
            'code' => $code,
            'name' => $name
        ]);
        if (!empty($existing)) {
            respond([
                'status' => 'error',
                'message' => 'Department name or department code already exists.'
            ], 400);
        }

        $payload = [
            'department_name' => $name,
            'department_code' => $code,
            'description' => $desc,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($headUserId) {
            $payload['department_head_user_id'] = $headUserId;
        }

        $newId = $db->insert('departments', $payload);

        $newDept = $db->select('departments', ['department_id' => $newId])[0] ?? $payload;

        // Audit Trail
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Create Department',
            'target_table'  => 'departments',
            'target_id'     => (string)$newId,
            'actor_user_id' => $userId,
            'department_id' => $newId,
            'old_data'      => null,
            'new_data'      => $newDept
        ]);

        respond([
            'status' => 'success',
            'message' => "Department \"{$name}\" created successfully.",
            'department_id' => $newId
        ], 201);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if (!$canEditDept) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to modify or edit departments.'
            ], 403);
        }

        $deptId = filter_var($data['department_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$deptId) {
            respond([
                'status' => 'error',
                'message' => 'Valid department_id is required.'
            ], 400);
        }

        $payload = ['updated_at' => date('Y-m-d H:i:s')];

        if (isset($data['department_name'])) $payload['department_name'] = trim($data['department_name']);
        if (isset($data['department_code'])) $payload['department_code'] = strtoupper(trim($data['department_code']));
        if (isset($data['description'])) $payload['description'] = trim($data['description']);
        if (isset($data['status']) && in_array($data['status'], ['Active', 'Inactive'])) $payload['status'] = $data['status'];

        if (array_key_exists('department_head_user_id', $data)) {
            $payload['department_head_user_id'] = filter_var($data['department_head_user_id'], FILTER_VALIDATE_INT) ?: null;
        }

        $oldDept = $db->select('departments', ['department_id' => $deptId])[0] ?? [];
        $db->update('departments', $payload, ['department_id' => $deptId]);
        $newDept = $db->select('departments', ['department_id' => $deptId])[0] ?? $payload;

        // Audit Trail
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Update Department',
            'target_table'  => 'departments',
            'target_id'     => (string)$deptId,
            'actor_user_id' => $userId,
            'department_id' => $deptId,
            'old_data'      => $oldDept,
            'new_data'      => $newDept
        ]);

        respond([
            'status' => 'success',
            'message' => 'Department updated successfully.'
        ]);
    }

    if ($method === 'DELETE') {
        if (!$canDeleteDept) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to delete departments.'
            ], 403);
        }

        $deptId = filter_var($_GET['department_id'] ?? $data['department_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$deptId) {
            respond([
                'status' => 'error',
                'message' => 'Valid department_id is required for deletion.'
            ], 400);
        }

        $oldDept = $db->select('departments', ['department_id' => $deptId])[0] ?? [];
        $db->delete('departments', ['department_id' => $deptId]);

        // Audit Trail
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Delete Department',
            'target_table'  => 'departments',
            'target_id'     => (string)$deptId,
            'actor_user_id' => $userId,
            'department_id' => $deptId,
            'old_data'      => $oldDept,
            'new_data'      => null
        ]);

        respond([
            'status' => 'success',
            'message' => 'Department deleted successfully.'
        ]);
    }

    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Departments API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
