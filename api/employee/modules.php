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

            // Gather all user's authorized departments (primary department + role_department_access)
            $userAuthorizedDeptNames = [];
            if (!empty($userDeptName)) {
                $userAuthorizedDeptNames[] = $userDeptName;
            }

            if ($userRoleId) {
                $rdaDepts = $db->query("
                    SELECT d.department_name 
                    FROM role_department_access rda
                    JOIN departments d ON rda.department_id = d.department_id
                    WHERE rda.role_id = :rid
                ", ['rid' => $userRoleId]) ?: [];
                foreach ($rdaDepts as $rd) {
                    if (!empty($rd['department_name'])) {
                        $userAuthorizedDeptNames[] = $rd['department_name'];
                    }
                }
            }

            $userAuthorizedDeptNames = array_unique($userAuthorizedDeptNames);

            $stopWords = ['department', 'office', 'bureau', 'division', 'service', 'services', 'and', '&', 'of', 'management', 'the', 'unit'];
            $deptKeywords = [];
            foreach ($userAuthorizedDeptNames as $dName) {
                $rawWords = preg_split('/[\s,\/&\-\+]+/', strtolower($dName));
                foreach ($rawWords as $w) {
                    if (strlen($w) >= 3 && !in_array($w, $stopWords, true)) {
                        $deptKeywords[] = $w;
                    }
                }
            }
            $deptKeywords = array_unique($deptKeywords);

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

        $moduleId = filter_var($data['module_id'] ?? ($_GET['module_id'] ?? null), FILTER_VALIDATE_INT);

        if (!$moduleId) {
            respond([
                'status' => 'error',
                'message' => 'Valid module_id is required.'
            ], 400);
        }

        $updatePayload = ['updated_at' => date('Y-m-d H:i:s')];

        if (isset($data['module_name'])) $updatePayload['module_name'] = trim($data['module_name']);
        if (isset($data['description'])) $updatePayload['description'] = trim($data['description']);
        if (isset($data['status'])) {
            $st = ucfirst(strtolower(trim($data['status'])));
            if (in_array($st, ['Active', 'Inactive', 'Archived'])) {
                $updatePayload['status'] = $st;
            }
        }

        $oldModuleRows = $db->query("SELECT * FROM modules WHERE module_id = :id", ['id' => $moduleId]);
        $oldModule = !empty($oldModuleRows) ? $oldModuleRows[0] : null;

        if (isset($updatePayload['status']) && $updatePayload['status'] === 'Archived') {
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

        $isPermanent = (isset($_GET['permanent']) && ($_GET['permanent'] === 'true' || $_GET['permanent'] === '1')) 
                     || (!empty($data['permanent']) && ($data['permanent'] === true || $data['permanent'] === 'true' || $data['permanent'] === 1))
                     || (isset($_GET['action']) && $_GET['action'] === 'permanent_delete')
                     || (!empty($data['action']) && $data['action'] === 'permanent_delete');

        $moduleIds = [];
        if (!empty($data['module_ids']) && is_array($data['module_ids'])) {
            $moduleIds = array_map('intval', array_filter($data['module_ids'], 'is_numeric'));
        } else {
            $singleId = filter_var($_GET['module_id'] ?? $data['module_id'] ?? null, FILTER_VALIDATE_INT);
            if ($singleId) {
                $moduleIds = [$singleId];
            }
        }

        if (empty($moduleIds)) {
            respond([
                'status' => 'error',
                'message' => 'Valid module_id or module_ids list is required.'
            ], 400);
        }

        $coreModuleIds = [4, 5, 6, 7, 8];

        if ($isPermanent) {
            // Guard core system modules
            $blockedCore = array_intersect($moduleIds, $coreModuleIds);
            if (!empty($blockedCore)) {
                respond([
                    'status' => 'error',
                    'message' => 'Core system modules cannot be permanently deleted from the database.'
                ], 400);
            }

            $deletedCount = 0;
            foreach ($moduleIds as $mId) {
                $oldModuleRows = $db->query("SELECT * FROM modules WHERE module_id = :id", ['id' => $mId]);
                $oldModule = !empty($oldModuleRows) ? $oldModuleRows[0] : null;

                if (!$oldModule) continue;

                // Explicit cascading cleanup of resources & permissions
                $resRows = $db->query("SELECT resource_id FROM resources WHERE module_id = :id", ['id' => $mId]) ?: [];
                foreach ($resRows as $r) {
                    $rId = (int)$r['resource_id'];
                    $db->query("DELETE FROM role_permissions WHERE permission_id IN (SELECT permission_id FROM permissions WHERE resource_id = :rid)", ['rid' => $rId]);
                    $db->delete('permissions', ['resource_id' => $rId]);
                }
                $db->delete('resources', ['module_id' => $mId]);

                // Delete module record
                $db->delete('modules', ['module_id' => $mId]);
                $deletedCount++;

                // Audit Trail
                \App\Services\AuditLogger::log([
                    'action'        => 'Permanent Delete Module',
                    'target_table'  => 'modules',
                    'target_id'     => (string)$mId,
                    'description'   => "Permanently deleted module ID {$mId} (\"" . ($oldModule['module_name'] ?? '') . "\") and associated resources/permissions",
                    'actor_user_id' => $userId,
                    'module_id'     => null
                ]);
            }

            respond([
                'status' => 'success',
                'message' => $deletedCount === 1 ? 'Module permanently deleted from database.' : "{$deletedCount} modules permanently deleted from database."
            ]);
        } else {
            // Soft Archive
            foreach ($moduleIds as $mId) {
                $oldModuleRows = $db->query("SELECT * FROM modules WHERE module_id = :id", ['id' => $mId]);
                $oldModule = !empty($oldModuleRows) ? $oldModuleRows[0] : null;

                $db->update('modules', [
                    'status' => 'Archived',
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['module_id' => $mId]);

                $db->update('resources', [
                    'status' => 'Archived',
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['module_id' => $mId]);

                $newModuleRows = $db->query("SELECT * FROM modules WHERE module_id = :id", ['id' => $mId]);
                $newModule = !empty($newModuleRows) ? $newModuleRows[0] : null;

                \App\Services\AuditLogger::logMutation([
                    'action'        => 'Archive Module',
                    'target_table'  => 'modules',
                    'target_id'     => (string)$mId,
                    'description'   => "Archived module ID {$mId} and all child resources",
                    'actor_user_id' => $userId,
                    'module_id'     => $mId,
                    'old_data'      => $oldModule,
                    'new_data'      => $newModule
                ]);
            }

            respond([
                'status' => 'success',
                'message' => 'Module(s) and child resources archived successfully.'
            ]);
        }
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
