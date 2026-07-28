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

    // Get logged in user details (department_id & role flags)
    $userRow = null;
    $userDeptId = null;
    $userDeptName = null;

    if ($userId) {
        $uRes = $db->query("
            SELECT u.user_id, u.role_id, p.department_id, d.department_name, r.role_prefix, r.role_name, r.is_global_access, r.is_superadmin
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN positions p ON u.position_id = p.position_id
            LEFT JOIN departments d ON p.department_id = d.department_id
            WHERE u.user_id = :uid
        ", ['uid' => $userId]);

        if (!empty($uRes)) {
            $userRow = $uRes[0];
            $userDeptId = $userRow['department_id'] ?? null;
            $userDeptName = $userRow['department_name'] ?? null;
        }
    }

    // Fetch user granted actions for Roles / Role & Permission Management resource
    $userPermRows = !empty($_SESSION['role_id']) ? ($db->query("
        SELECT DISTINCT UPPER(a.action_name) AS action_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.permission_id
        JOIN actions a ON p.action_id = a.action_id
        JOIN resources res ON p.resource_id = res.resource_id
        WHERE rp.role_id = :rid AND (LOWER(res.resource_name) LIKE '%role%' OR LOWER(res.resource_name) LIKE '%permission%')
    ", ['rid' => $_SESSION['role_id']]) ?: []) : [];

    $userGrantedActions = [];
    foreach ($userPermRows as $pr) {
        if (!empty($pr['action_name'])) $userGrantedActions[] = strtoupper($pr['action_name']);
    }

    $isSuperAdmin = false;
    if (!empty($userRow)) {
        $rolePrefix = strtoupper($userRow['role_prefix'] ?? '');
        $roleName = strtolower($userRow['role_name'] ?? '');
        if ((!empty($userRow['is_superadmin']) && intval($userRow['is_superadmin']) === 1) || !empty($userRow['is_global_access']) || in_array($rolePrefix, ['SA', 'SADM']) || $roleName === 'super administrator' || $roleName === 'superadmin') {
            $isSuperAdmin = true;
        }
    }

    $canCreateRole = $isSuperAdmin || in_array('CREATE', $userGrantedActions);
    $canEditRole   = $isSuperAdmin || in_array('EDIT', $userGrantedActions);
    $canDeleteRole = $isSuperAdmin || in_array('DELETE', $userGrantedActions);

    if ($method === 'GET') {
        if ($isSuperAdmin) {
            $roles = $db->query("SELECT role_id, role_name, role_prefix, is_global_access, description, status, is_system_role, department_id, created_at, updated_at FROM roles ORDER BY role_id ASC");
        } else if ($userDeptId) {
            if (empty($userDeptName)) {
                $dRow = $db->query("SELECT department_name FROM departments WHERE department_id = :did", ['did' => $userDeptId]);
                if (!empty($dRow)) $userDeptName = $dRow[0]['department_name'];
            }

            $rawWords = preg_split('/[\s,\/&\-\+]+/', strtolower($userDeptName ?? ''));
            $stopWords = ['department', 'office', 'bureau', 'division', 'service', 'services', 'and', '&', 'of', 'management', 'the', 'unit'];
            $keywords = array_filter($rawWords, function($w) use ($stopWords) {
                return strlen($w) >= 3 && !in_array($w, $stopWords);
            });

            $sql = "
                SELECT DISTINCT r.role_id, r.role_name, r.role_prefix, r.is_global_access, r.description, r.status, r.is_system_role, r.department_id, r.created_at, r.updated_at
                FROM roles r
                LEFT JOIN role_department_access rda ON r.role_id = rda.role_id
                WHERE (r.department_id = :did OR rda.department_id = :did2
            ";

            $params = ['did' => $userDeptId, 'did2' => $userDeptId];
            $idx = 0;
            foreach ($keywords as $kw) {
                $pName = "kw_" . $idx++;
                $sql .= " OR LOWER(r.role_name) LIKE :{$pName}";
                $params[$pName] = '%' . strtolower($kw) . '%';
            }
            $sql .= ") ORDER BY r.role_id ASC";

            $roles = $db->query($sql, $params);
        } else {
            $roles = $db->query("SELECT role_id, role_name, role_prefix, is_global_access, description, status, is_system_role, department_id, created_at, updated_at FROM roles WHERE is_global_access = 0 ORDER BY role_id ASC");
        }

        respond([
            'status' => 'success',
            'data' => $roles ?: [],
            'current_user' => [
                'user_id' => (int)$userId,
                'department_id' => $userDeptId,
                'department_name' => $userDeptName,
                'is_superadmin' => $isSuperAdmin,
                'granted_actions' => $userGrantedActions
            ]
        ]);
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?? $_POST;

    if ($method === 'POST') {
        if (!$canCreateRole) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to create system roles.'
            ], 403);
        }

        // Global access can ONLY be granted by Superadmin!
        $isGlobalAccess = ($isSuperAdmin && !empty($data['is_global_access'])) ? 1 : 0;
        $status = in_array($data['status'] ?? '', ['Active', 'Inactive']) ? $data['status'] : 'Active';

        $roleName = trim($data['role_name'] ?? '');
        $rolePrefix = strtoupper(trim($data['role_prefix'] ?? ''));
        $description = trim($data['description'] ?? '');

        if (empty($roleName) || empty($rolePrefix)) {
            respond([
                'status' => 'error',
                'message' => 'Role Name and Role Code/Prefix are required.'
            ], 400);
        }

        // Check duplicate name or prefix
        $existing = $db->query("SELECT role_id FROM roles WHERE LOWER(role_name) = LOWER(:rname) OR UPPER(role_prefix) = UPPER(:rpref)", [
            'rname' => $roleName,
            'rpref' => $rolePrefix
        ]);
        if (!empty($existing)) {
            respond([
                'status' => 'error',
                'message' => 'Role name or role code/prefix already exists.'
            ], 400);
        }

        $insertPayload = [
            'role_name' => $roleName,
            'role_prefix' => $rolePrefix,
            'description' => $description ?: null,
            'is_global_access' => $isGlobalAccess,
            'status' => $status,
            'is_system_role' => 0,
            'department_id' => $userDeptId ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $newId = $db->insert('roles', $insertPayload);

        // Audit Trail
        try {
            $db->insert('audit_logs', [
                'actor_user_id' => $userId,
                'action' => 'Create Role',
                'target_table' => 'roles',
                'target_id' => (string)$newId,
                'description' => "Created role \"{$roleName}\" ({$rolePrefix})",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'request_method' => 'POST',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/roles.php',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'status' => 'Success'
            ]);
        } catch (Throwable $auditEx) {}

        respond([
            'status' => 'success',
            'message' => "Role \"{$roleName}\" created successfully.",
            'role_id' => $newId
        ], 201);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if (!$canEditRole) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to modify or edit system roles.'
            ], 403);
        }

        $roleId = filter_var($data['role_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$roleId) {
            respond([
                'status' => 'error',
                'message' => 'Valid role_id is required.'
            ], 400);
        }

        $targetRoles = $db->select('roles', ['role_id' => $roleId]);
        if (empty($targetRoles)) {
            respond([
                'status' => 'error',
                'message' => 'Target role not found.'
            ], 404);
        }

        $targetRole = $targetRoles[0];

        $updatePayload = ['updated_at' => date('Y-m-d H:i:s')];

        if (isset($data['role_name'])) $updatePayload['role_name'] = trim($data['role_name']);
        if (isset($data['description'])) $updatePayload['description'] = trim($data['description']);
        
        // Global access can ONLY be granted or modified by Superadmin!
        if (isset($data['is_global_access']) && $isSuperAdmin) {
            $updatePayload['is_global_access'] = !empty($data['is_global_access']) ? 1 : 0;
        }
        
        if (isset($data['status']) && in_array($data['status'], ['Active', 'Inactive', 'Archived'])) {
            // Prevent deactivating system role
            if (!empty($targetRole['is_system_role']) && in_array($data['status'], ['Inactive', 'Archived'])) {
                respond([
                    'status' => 'error',
                    'message' => 'System critical roles cannot be deactivated or archived.'
                ], 403);
            }
            $updatePayload['status'] = $data['status'];
        }

        $db->update('roles', $updatePayload, ['role_id' => $roleId]);

        // Audit Trail
        try {
            $db->insert('audit_logs', [
                'actor_user_id' => $userId,
                'action' => 'Update Role',
                'target_table' => 'roles',
                'target_id' => (string)$roleId,
                'description' => "Updated role ID {$roleId}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'request_method' => $method,
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/roles.php',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'status' => 'Success'
            ]);
        } catch (Throwable $auditEx) {}

        respond([
            'status' => 'success',
            'message' => 'Role updated successfully.'
        ]);
    }

    if ($method === 'DELETE') {
        if (!$canDeleteRole) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to delete or archive system roles.'
            ], 403);
        }
        $roleId = filter_var($_GET['role_id'] ?? $data['role_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$roleId) {
            respond([
                'status' => 'error',
                'message' => 'Valid role_id is required for deletion.'
            ], 400);
        }

        $targetRoles = $db->select('roles', ['role_id' => $roleId]);
        if (empty($targetRoles)) {
            respond([
                'status' => 'error',
                'message' => 'Target role not found.'
            ], 404);
        }

        $targetRole = $targetRoles[0];

        // System role protection
        if (
            !empty($targetRole['is_system_role']) || 
            in_array(strtoupper($targetRole['role_prefix'] ?? ''), ['SA', 'SADM'])
        ) {
            respond([
                'status' => 'error',
                'message' => 'System critical roles cannot be archived.'
            ], 403);
        }

        $db->update('roles', [
            'status' => 'Archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['role_id' => $roleId]);

        // Audit Trail
        try {
            $db->insert('audit_logs', [
                'actor_user_id' => $userId,
                'action' => 'Archive Role',
                'target_table' => 'roles',
                'target_id' => (string)$roleId,
                'description' => "Archived role ID {$roleId}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'request_method' => 'DELETE',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/roles.php',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'status' => 'Success'
            ]);
        } catch (Throwable $auditEx) {}

        respond([
            'status' => 'success',
            'message' => 'Role archived successfully.'
        ]);
    }

    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Roles API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
