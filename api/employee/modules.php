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

    // Fetch user granted actions for Module Management resource
    $userPermRows = !empty($_SESSION['role_id']) ? ($db->query("
        SELECT DISTINCT UPPER(a.action_name) AS action_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.permission_id
        JOIN actions a ON p.action_id = a.action_id
        JOIN resources res ON p.resource_id = res.resource_id
        WHERE rp.role_id = :rid AND (LOWER(res.resource_name) LIKE '%module%' OR LOWER(res.resource_name) LIKE '%permission%')
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

    $canCreateModule = $isSuperAdmin || in_array('CREATE', $userGrantedActions);
    $canEditModule   = $isSuperAdmin || in_array('EDIT', $userGrantedActions);
    $canDeleteModule = $isSuperAdmin || in_array('DELETE', $userGrantedActions);

    // Get logged in user details (department_id & department_name)
    $userRoleId = $_SESSION['role_id'] ?? null;
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
            $userRoleId = $userRow['role_id'] ?? $userRoleId;
        }
    }

    if ($method === 'GET') {
        if ($isSuperAdmin) {
            $modules = $db->query("SELECT module_id, module_name, description, status, created_at, updated_at FROM modules ORDER BY module_id ASC") ?: [];
        } else {
            $coreModuleIds = [4, 5, 6, 7, 8];

            if (empty($userDeptName) && $userDeptId) {
                $dRow = $db->query("SELECT department_name FROM departments WHERE department_id = :did", ['did' => $userDeptId]);
                if (!empty($dRow)) $userDeptName = $dRow[0]['department_name'];
            }

            $rawWords = preg_split('/[\s,\/&\-\+]+/', strtolower($userDeptName ?? ''));
            $stopWords = ['department', 'office', 'bureau', 'division', 'service', 'services', 'and', '&', 'of', 'management', 'the', 'unit'];
            $deptKeywords = array_filter($rawWords, function($w) use ($stopWords) {
                return strlen($w) >= 3 && !in_array($w, $stopWords);
            });

            // Fetch ALL modules so frontend Active and Archived tabs work properly
            $allModules = $db->query("SELECT module_id, module_name, description, status, created_at, updated_at FROM modules ORDER BY module_id ASC") ?: [];

            $grantedModRows = $userRoleId ? ($db->query("
                SELECT DISTINCT res.module_id
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.permission_id
                JOIN resources res ON p.resource_id = res.resource_id
                WHERE rp.role_id = :rid
            ", ['rid' => $userRoleId]) ?: []) : [];

            $grantedModIds = array_column($grantedModRows, 'module_id');

            $modules = [];
            foreach ($allModules as $m) {
                $mId = (int)$m['module_id'];
                $mNameLower = strtolower($m['module_name']);

                if (in_array($mId, $coreModuleIds, true) || in_array($mId, $grantedModIds, true)) {
                    $modules[] = $m;
                    continue;
                }

                $matchesKeyword = false;
                foreach ($deptKeywords as $kw) {
                    if (strpos($mNameLower, $kw) !== false) {
                        $matchesKeyword = true;
                        break;
                    }
                }

                if ($matchesKeyword) {
                    $modules[] = $m;
                }
            }
        }

        respond([
            'status' => 'success',
            'data' => $modules ?: [],
            'current_user' => [
                'user_id' => (int)$userId,
                'is_superadmin' => $isSuperAdmin,
                'granted_actions' => $userGrantedActions
            ]
        ]);
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?? $_POST;

    if ($method === 'POST') {
        if (!$canCreateModule) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to create system modules.'
            ], 403);
        }
        // Create Module
        $moduleName = trim($data['module_name'] ?? '');
        $description = trim($data['description'] ?? '');
        $status = in_array($data['status'] ?? '', ['Active', 'Inactive']) ? $data['status'] : 'Active';

        if (empty($moduleName)) {
            respond([
                'status' => 'error',
                'message' => 'Module Name is required.'
            ], 400);
        }

        // Check duplicate module name
        $existing = $db->query("SELECT module_id FROM modules WHERE LOWER(module_name) = LOWER(:name)", ['name' => $moduleName]);
        if (!empty($existing)) {
            respond([
                'status' => 'error',
                'message' => 'A module with this name already exists.'
            ], 400);
        }

        $insertPayload = [
            'module_name' => $moduleName,
            'description' => $description,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $newId = $db->insert('modules', $insertPayload);

        // Audit Trail
        \App\Services\AuditLogger::log([
            'action'        => 'Create Module',
            'target_table'  => 'modules',
            'target_id'     => (string)$newId,
            'description'   => "Created module \"{$moduleName}\"",
            'actor_user_id' => $userId,
            'module_id'     => $newId
        ]);

        respond([
            'status' => 'success',
            'message' => "Module \"{$moduleName}\" created successfully.",
            'module_id' => $newId
        ], 201);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if (!$canEditModule) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to modify or edit system modules.'
            ], 403);
        }

        $moduleId = filter_var($data['module_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$moduleId) {
            respond([
                'status' => 'error',
                'message' => 'Valid module_id is required.'
            ], 400);
        }

        $updatePayload = ['updated_at' => date('Y-m-d H:i:s')];

        if (isset($data['module_name'])) $updatePayload['module_name'] = trim($data['module_name']);
        if (isset($data['description'])) $updatePayload['description'] = trim($data['description']);
        if (isset($data['status']) && in_array($data['status'], ['Active', 'Inactive', 'Archived'])) {
            $updatePayload['status'] = $data['status'];
        }

        $oldModuleRows = $db->query("SELECT * FROM modules WHERE module_id = :id", ['id' => $moduleId]);
        $oldModule = !empty($oldModuleRows) ? $oldModuleRows[0] : null;

        if (isset($data['status']) && $data['status'] === 'Archived') {
            $db->update('resources', ['status' => 'Archived', 'updated_at' => date('Y-m-d H:i:s')], ['module_id' => $moduleId]);
        }

        $db->update('modules', $updatePayload, ['module_id' => $moduleId]);

        $newModuleRows = $db->query("SELECT * FROM modules WHERE module_id = :id", ['id' => $moduleId]);
        $newModule = !empty($newModuleRows) ? $newModuleRows[0] : null;

        // Audit Trail with Full Data Mutation Snapshot
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Update Module',
            'target_table'  => 'modules',
            'target_id'     => (string)$moduleId,
            'description'   => "Updated module ID {$moduleId}",
            'actor_user_id' => $userId,
            'module_id'     => $moduleId,
            'old_data'      => $oldModule,
            'new_data'      => $newModule
        ]);

        respond([
            'status' => 'success',
            'message' => 'Module updated successfully.'
        ]);
    }

    if ($method === 'DELETE') {
        if (!$canDeleteModule) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to delete or archive system modules.'
            ], 403);
        }

        $moduleId = filter_var($_GET['module_id'] ?? $data['module_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$moduleId) {
            respond([
                'status' => 'error',
                'message' => 'Valid module_id is required for deletion.'
            ], 400);
        }

        $oldModuleRows = $db->query("SELECT * FROM modules WHERE module_id = :id", ['id' => $moduleId]);
        $oldModule = !empty($oldModuleRows) ? $oldModuleRows[0] : null;

        $db->update('modules', [
            'status' => 'Archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['module_id' => $moduleId]);

        // Cascade archive all child resources under this module
        $db->update('resources', [
            'status' => 'Archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['module_id' => $moduleId]);

        $newModuleRows = $db->query("SELECT * FROM modules WHERE module_id = :id", ['id' => $moduleId]);
        $newModule = !empty($newModuleRows) ? $newModuleRows[0] : null;

        // Audit Trail with Full Data Mutation Snapshot
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Archive Module',
            'target_table'  => 'modules',
            'target_id'     => (string)$moduleId,
            'description'   => "Archived module ID {$moduleId} and all child resources",
            'actor_user_id' => $userId,
            'module_id'     => $moduleId,
            'old_data'      => $oldModule,
            'new_data'      => $newModule
        ]);

        respond([
            'status' => 'success',
            'message' => 'Module and all child resources archived successfully.'
        ]);
    }

    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Modules API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
