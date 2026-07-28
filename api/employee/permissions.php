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

    // Fetch user granted actions for Permission Builder / Role Permission Matrix resource
    $userPermRows = !empty($_SESSION['role_id']) ? ($db->query("
        SELECT DISTINCT UPPER(a.action_name) AS action_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.permission_id
        JOIN actions a ON p.action_id = a.action_id
        JOIN resources res ON p.resource_id = res.resource_id
        WHERE rp.role_id = :rid AND (LOWER(res.resource_name) LIKE '%permission%' OR LOWER(res.resource_name) LIKE '%matrix%' OR LOWER(res.resource_name) LIKE '%builder%')
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

    $canEditPermissions = $isSuperAdmin || in_array('EDIT', $userGrantedActions) || in_array('CREATE', $userGrantedActions);

    if ($method === 'GET') {
        if ($isSuperAdmin) {
            $roles = $db->query("
                SELECT r.role_id, r.role_name, r.role_prefix, r.is_global_access, r.description, r.status, r.is_system_role, r.department_id, d.department_name 
                FROM roles r
                LEFT JOIN departments d ON r.department_id = d.department_id 
                ORDER BY r.role_id ASC
            ");
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
                SELECT DISTINCT r.role_id, r.role_name, r.role_prefix, r.is_global_access, r.description, r.status, r.is_system_role, r.department_id, d.department_name
                FROM roles r
                LEFT JOIN departments d ON r.department_id = d.department_id
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
            $roles = $db->query("
                SELECT r.role_id, r.role_name, r.role_prefix, r.is_global_access, r.description, r.status, r.is_system_role, r.department_id, d.department_name 
                FROM roles r
                LEFT JOIN departments d ON r.department_id = d.department_id
                WHERE r.is_global_access = 0 
                ORDER BY r.role_id ASC
            ");
        }

        $departments = $db->query("SELECT department_id, department_code, department_name FROM departments WHERE status = 'Active' ORDER BY department_name ASC") ?: [];
        $modules = $db->query("SELECT module_id, module_name, description, status FROM modules WHERE status != 'Archived' OR status IS NULL ORDER BY module_id ASC");
        $resources = $db->query("SELECT resource_id, module_id, resource_name, resource_route, status FROM resources WHERE status != 'Archived' OR status IS NULL ORDER BY resource_id ASC");
        $actions = $db->query("SELECT action_id, action_name FROM actions WHERE status != 'Archived' OR status IS NULL ORDER BY action_id ASC");
        $permissions = $db->query("SELECT permission_id, resource_id, action_id, permission_key, status FROM permissions");
        $rolePermissions = $db->query("SELECT role_permission_id, role_id, permission_id, granted_by, granted_at FROM role_permissions");

        respond([
            'status' => 'success',
            'roles' => $roles ?: [],
            'departments' => $departments ?: [],
            'modules' => $modules ?: [],
            'resources' => $resources ?: [],
            'actions' => $actions ?: [],
            'permissions' => $permissions ?: [],
            'role_permissions' => $rolePermissions ?: [],
            'current_user' => [
                'user_id' => (int)$userId,
                'department_id' => $userDeptId,
                'department_name' => $userDeptName,
                'is_superadmin' => $isSuperAdmin,
                'granted_actions' => $userGrantedActions
            ]
        ]);
    }

    if ($method === 'POST') {
        if (!$canEditPermissions) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to modify system role permissions.'
            ], 403);
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true) ?? [];

        $roleId = filter_var($data['role_id'] ?? null, FILTER_VALIDATE_INT);
        $grantedPairs = is_array($data['granted_permissions'] ?? null) ? $data['granted_permissions'] : [];

        if (!$roleId) {
            respond([
                'status' => 'error',
                'message' => 'Valid role_id is required.'
            ], 400);
        }

        // If non-superadmin, check if target role belongs to user's department scope
        if (!$isSuperAdmin && $userDeptId) {
            $targetRoleCheck = $db->query("
                SELECT r.role_id FROM roles r
                LEFT JOIN role_department_access rda ON r.role_id = rda.role_id
                WHERE r.role_id = :targetId AND (r.department_id = :did OR rda.department_id = :did2)
            ", ['targetId' => $roleId, 'did' => $userDeptId, 'did2' => $userDeptId]);

            if (empty($targetRoleCheck)) {
                respond([
                    'status' => 'error',
                    'message' => 'Forbidden. You cannot modify permissions for roles outside your department.'
                ], 403);
            }
        }

        // Check target role exists
        $targetRoles = $db->select('roles', ['role_id' => $roleId]);
        if (empty($targetRoles)) {
            respond([
                'status' => 'error',
                'message' => 'Target role not found.'
            ], 404);
        }

        $targetRole = $targetRoles[0];

        // 4. Database Transaction (Atomic Operation)
        $pdo = $db->getPdo();
        $pdo->beginTransaction();

        try {
            // A. Fetch existing permissions lookup map
            $existingPerms = $db->query("SELECT permission_id, resource_id, action_id FROM permissions") ?: [];
            $permLookup = [];
            foreach ($existingPerms as $p) {
                $key = $p['resource_id'] . '_' . $p['action_id'];
                $permLookup[$key] = $p['permission_id'];
            }

            // B. Process granted (resource_id, action_id) pairs
            $grantedPermIds = [];
            foreach ($grantedPairs as $pair) {
                $rId = filter_var($pair['resource_id'] ?? null, FILTER_VALIDATE_INT);
                $aId = filter_var($pair['action_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$rId || !$aId) continue;

                $key = $rId . '_' . $aId;
                if (isset($permLookup[$key])) {
                    $grantedPermIds[] = $permLookup[$key];
                } else {
                    // Create new permission row
                    $newPermKey = "res_{$rId}_act_{$aId}";
                    $newId = $db->insert('permissions', [
                        'resource_id' => $rId,
                        'action_id' => $aId,
                        'permission_key' => $newPermKey,
                        'status' => 'Active',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    if ($newId) {
                        $permLookup[$key] = $newId;
                        $grantedPermIds[] = $newId;
                    }
                }
            }

            // C. Clear existing role_permissions for this role_id
            $db->delete('role_permissions', ['role_id' => $roleId]);

            // D. Insert new granted role_permissions
            $grantedPermIds = array_unique($grantedPermIds);
            foreach ($grantedPermIds as $pId) {
                $db->insert('role_permissions', [
                    'role_id' => $roleId,
                    'permission_id' => $pId,
                    'granted_by' => $userId,
                    'granted_at' => date('Y-m-d H:i:s')
                ]);
            }

            // E. Record Audit Trail
            try {
                $db->insert('audit_logs', [
                    'actor_user_id' => $userId,
                    'action' => 'Update Role Permissions Matrix',
                    'target_table' => 'roles',
                    'target_id' => (string)$roleId,
                    'description' => "Updated permissions matrix for role \"{$targetRole['role_name']}\" (" . count($grantedPermIds) . " permissions granted)",
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    'request_method' => 'POST',
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/permissions.php',
                    'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    'status' => 'Success'
                ]);
            } catch (Throwable $auditEx) {}

            $pdo->commit();

            respond([
                'status' => 'success',
                'message' => "Role permissions for \"{$targetRole['role_name']}\" updated successfully."
            ]);

        } catch (Throwable $txEx) {
            $pdo->rollBack();
            throw $txEx;
        }
    }

    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Permissions API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
