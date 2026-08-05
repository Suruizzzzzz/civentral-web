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

// Auto-grant permissions for a resource to creator, department roles & Super Admin roles
function autoGrantResourcePermissions($db, int $resourceId, ?int $actorUserId = null, ?int $creatorRoleId = null, ?int $creatorDeptId = null): void {
    try {
        $grantedByUserId = null;
        if ($actorUserId) {
            $uCheck = $db->query("SELECT user_id FROM users WHERE user_id = :uid", ['uid' => $actorUserId]);
            if (!empty($uCheck)) {
                $grantedByUserId = (int)$actorUserId;
            }
        }

        $allActions = $db->query("SELECT action_id FROM actions WHERE status != 'Archived' OR status IS NULL") ?: [];
        if (empty($allActions)) return;

        $targetRoleIds = [];
        if ($creatorRoleId) {
            $targetRoleIds[] = (int)$creatorRoleId;
        }

        if ($creatorDeptId) {
            $deptRoles = $db->query("SELECT role_id FROM roles WHERE department_id = :did", ['did' => $creatorDeptId]) ?: [];
            foreach ($deptRoles as $dr) {
                if (!empty($dr['role_id'])) $targetRoleIds[] = (int)$dr['role_id'];
            }
        }

        $superRoles = $db->query("
            SELECT role_id 
            FROM roles 
            WHERE is_superadmin = 1 
               OR is_global_access = 1 
               OR UPPER(role_prefix) IN ('SA', 'SADM') 
               OR LOWER(role_name) IN ('super administrator', 'superadmin')
        ") ?: [];

        foreach ($superRoles as $sr) {
            if (!empty($sr['role_id'])) $targetRoleIds[] = (int)$sr['role_id'];
        }

        $targetRoleIds = array_unique(array_filter($targetRoleIds));

        foreach ($allActions as $actRow) {
            $actId = (int)$actRow['action_id'];
            $permKey = "res_{$resourceId}_act_{$actId}";

            $existingPerm = $db->query(
                "SELECT permission_id FROM permissions WHERE resource_id = :rid AND action_id = :aid",
                ['rid' => $resourceId, 'aid' => $actId]
            );

            if (!empty($existingPerm)) {
                $permId = (int)$existingPerm[0]['permission_id'];
            } else {
                $permId = $db->insert('permissions', [
                    'resource_id' => $resourceId,
                    'action_id' => $actId,
                    'permission_key' => $permKey,
                    'status' => 'Active',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            if ($permId) {
                foreach ($targetRoleIds as $rId) {
                    $existingRp = $db->query(
                        "SELECT role_permission_id FROM role_permissions WHERE role_id = :rid AND permission_id = :pid",
                        ['rid' => $rId, 'pid' => $permId]
                    );
                    if (empty($existingRp)) {
                        $db->insert('role_permissions', [
                            'role_id' => $rId,
                            'permission_id' => $permId,
                            'granted_by' => $grantedByUserId,
                            'granted_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }
    } catch (Throwable $e) {
        error_log("autoGrantResourcePermissions Error: " . $e->getMessage());
    }
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

    // Fetch user granted actions for Resource Management resource
    $userPermRows = !empty($_SESSION['role_id']) ? ($db->query("
        SELECT DISTINCT UPPER(a.action_name) AS action_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.permission_id
        JOIN actions a ON p.action_id = a.action_id
        JOIN resources res ON p.resource_id = res.resource_id
        WHERE rp.role_id = :rid AND (LOWER(res.resource_name) LIKE '%resource%' OR LOWER(res.resource_name) LIKE '%permission%')
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

    $canCreateResource = $isSuperAdmin || in_array('CREATE', $userGrantedActions);
    $canEditResource   = $isSuperAdmin || in_array('EDIT', $userGrantedActions);
    $canDeleteResource = $isSuperAdmin || in_array('DELETE', $userGrantedActions);

    if ($method === 'GET') {
        // Fetch user department info
        $userRoleId = $_SESSION['role_id'] ?? null;
        $userDeptId = null;
        $userDeptName = null;

        if ($userId) {
            $uRes = $db->query("
                SELECT u.user_id, u.role_id, p.department_id, d.department_name
                FROM users u
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

        if ($isSuperAdmin) {
            $resourcesRaw = $db->query("
                SELECT r.*, m.module_name, m.description AS module_desc
                FROM resources r
                LEFT JOIN modules m ON r.module_id = m.module_id
                ORDER BY r.resource_id ASC
            ") ?: [];
            $modules = $db->query("SELECT module_id, module_name FROM modules WHERE status != 'Archived' OR status IS NULL ORDER BY module_name ASC") ?: [];
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

            $allModules = $db->query("SELECT module_id, module_name FROM modules WHERE status != 'Archived' OR status IS NULL ORDER BY module_id ASC") ?: [];

            $grantedModRows = $userRoleId ? ($db->query("
                SELECT DISTINCT res.module_id
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.permission_id
                JOIN resources res ON p.resource_id = res.resource_id
                WHERE rp.role_id = :rid
            ", ['rid' => $userRoleId]) ?: []) : [];

            $grantedModIds = array_column($grantedModRows, 'module_id');

            $modules = [];
            $allowedModuleIds = [];
            foreach ($allModules as $m) {
                $mId = (int)$m['module_id'];
                $mNameLower = strtolower($m['module_name']);

                if (in_array($mId, $coreModuleIds, true) || in_array($mId, $grantedModIds, true)) {
                    $modules[] = $m;
                    $allowedModuleIds[] = $mId;
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
                    $allowedModuleIds[] = $mId;
                }
            }

            if (!empty($allowedModuleIds)) {
                $inClause = implode(',', array_map('intval', $allowedModuleIds));
                $sql = "SELECT r.*, m.module_name, m.description AS module_desc
                        FROM resources r
                        LEFT JOIN modules m ON r.module_id = m.module_id
                        WHERE r.module_id IN ({$inClause})
                        ORDER BY r.resource_id ASC";
                $resourcesRaw = $db->query($sql) ?: [];
            } else {
                $resourcesRaw = [];
            }
        }

        $resources = [];
        foreach ($resourcesRaw as $row) {
            $resources[] = [
                'resource_id' => $row['resource_id'],
                'module_id' => $row['module_id'],
                'resource_name' => $row['resource_name'],
                'resource_route' => $row['resource_route'],
                'description' => $row['description'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'modules' => $row['module_id'] ? [
                    'module_id' => $row['module_id'],
                    'module_name' => $row['module_name']
                ] : null
            ];
        }

        respond([
            'status' => 'success',
            'data' => $resources,
            'modules' => $modules,
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
        if (!$canCreateResource) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to create system resources.'
            ], 403);
        }
        // Create Resource
        $moduleId = filter_var($data['module_id'] ?? null, FILTER_VALIDATE_INT);
        $resourceName = trim($data['resource_name'] ?? '');
        $resourceRoute = trim($data['resource_route'] ?? '');
        $description = trim($data['description'] ?? '');
        $status = in_array($data['status'] ?? '', ['Active', 'Inactive']) ? $data['status'] : 'Active';

        if (!$moduleId || empty($resourceName)) {
            respond([
                'status' => 'error',
                'message' => 'Parent Module and Resource Name are required.'
            ], 400);
        }

        // Validate parent module exists
        $modCheck = $db->select('modules', ['module_id' => $moduleId]);
        if (empty($modCheck)) {
            respond([
                'status' => 'error',
                'message' => 'Selected parent module does not exist.'
            ], 404);
        }

        // Duplicate check within module
        $existing = $db->query(
            "SELECT resource_id FROM resources WHERE module_id = :mid AND LOWER(resource_name) = LOWER(:rname)",
            ['mid' => $moduleId, 'rname' => $resourceName]
        );
        if (!empty($existing)) {
            respond([
                'status' => 'error',
                'message' => 'A resource with this name already exists in the selected module.'
            ], 400);
        }

        $insertPayload = [
            'module_id' => $moduleId,
            'resource_name' => $resourceName,
            'resource_route' => $resourceRoute ?: null,
            'description' => $description ?: null,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $newId = $db->insert('resources', $insertPayload);

        // Fetch user department ID
        $userDeptId = null;
        if ($userId) {
            $uRes = $db->query("SELECT p.department_id FROM users u LEFT JOIN positions p ON u.position_id = p.position_id WHERE u.user_id = :uid", ['uid' => $userId]);
            if (!empty($uRes)) $userDeptId = $uRes[0]['department_id'] ?? null;
        }

        // Auto-grant permissions for the new resource to creator, department roles & Super Admin roles
        autoGrantResourcePermissions($db, (int)$newId, (int)$userId, $_SESSION['role_id'] ?? null, $userDeptId);

        // Audit Trail
        \App\Services\AuditLogger::log([
            'action'        => 'Create Resource',
            'target_table'  => 'resources',
            'target_id'     => (string)$newId,
            'description'   => "Created resource \"{$resourceName}\" under module ID {$moduleId}",
            'actor_user_id' => $userId,
            'resource_id'   => $newId,
            'module_id'     => $moduleId
        ]);

        respond([
            'status' => 'success',
            'message' => "Resource \"{$resourceName}\" created successfully.",
            'resource_id' => $newId
        ], 201);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if (!$canEditResource) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to modify or edit system resources.'
            ], 403);
        }

        $resourceId = filter_var($data['resource_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$resourceId) {
            respond([
                'status' => 'error',
                'message' => 'Valid resource_id is required.'
            ], 400);
        }

        $updatePayload = ['updated_at' => date('Y-m-d H:i:s')];

        if (isset($data['module_id'])) {
            $mid = filter_var($data['module_id'], FILTER_VALIDATE_INT);
            if ($mid) $updatePayload['module_id'] = $mid;
        }
        if (isset($data['resource_name'])) $updatePayload['resource_name'] = trim($data['resource_name']);
        if (isset($data['resource_route'])) $updatePayload['resource_route'] = trim($data['resource_route']);
        if (isset($data['description'])) $updatePayload['description'] = trim($data['description']);
        if (isset($data['status'])) {
            $st = ucfirst(strtolower(trim($data['status'])));
            if (in_array($st, ['Active', 'Inactive', 'Archived'])) {
                $updatePayload['status'] = $st;
            }
        }

        $oldResRows = $db->query("SELECT * FROM resources WHERE resource_id = :id", ['id' => $resourceId]);
        $oldRes = !empty($oldResRows) ? $oldResRows[0] : null;

        $db->update('resources', $updatePayload, ['resource_id' => $resourceId]);

        $newResRows = $db->query("SELECT * FROM resources WHERE resource_id = :id", ['id' => $resourceId]);
        $newRes = !empty($newResRows) ? $newResRows[0] : null;

        // Auto-grant permissions for the resource to creator, department roles & Super Admin roles
        $userDeptId = null;
        if ($userId) {
            $uRes = $db->query("SELECT p.department_id FROM users u LEFT JOIN positions p ON u.position_id = p.position_id WHERE u.user_id = :uid", ['uid' => $userId]);
            if (!empty($uRes)) $userDeptId = $uRes[0]['department_id'] ?? null;
        }
        autoGrantResourcePermissions($db, (int)$resourceId, (int)$userId, $_SESSION['role_id'] ?? null, $userDeptId);

        // Audit Trail with Full Data Mutation Snapshot
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Update Resource',
            'target_table'  => 'resources',
            'target_id'     => (string)$resourceId,
            'description'   => "Updated resource ID {$resourceId}",
            'actor_user_id' => $userId,
            'resource_id'   => $resourceId,
            'old_data'      => $oldRes,
            'new_data'      => $newRes
        ]);

        respond([
            'status' => 'success',
            'message' => 'Resource updated successfully.'
        ]);
    }

    if ($method === 'DELETE') {
        if (!$canDeleteResource) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to delete or archive system resources.'
            ], 403);
        }

        $resourceId = filter_var($_GET['resource_id'] ?? $data['resource_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$resourceId) {
            respond([
                'status' => 'error',
                'message' => 'Valid resource_id is required for deletion.'
            ], 400);
        }

        $oldResRows = $db->query("SELECT * FROM resources WHERE resource_id = :id", ['id' => $resourceId]);
        $oldRes = !empty($oldResRows) ? $oldResRows[0] : null;

        $db->update('resources', [
            'status' => 'Archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['resource_id' => $resourceId]);

        $newResRows = $db->query("SELECT * FROM resources WHERE resource_id = :id", ['id' => $resourceId]);
        $newRes = !empty($newResRows) ? $newResRows[0] : null;

        // Audit Trail with Full Data Mutation Snapshot
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Archive Resource',
            'target_table'  => 'resources',
            'target_id'     => (string)$resourceId,
            'description'   => "Archived resource ID {$resourceId}",
            'actor_user_id' => $userId,
            'resource_id'   => $resourceId,
            'old_data'      => $oldRes,
            'new_data'      => $newRes
        ]);

        $db->update('resources', [
            'status' => 'Archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['resource_id' => $resourceId]);

        // Audit Trail
        \App\Services\AuditLogger::log([
            'action'        => 'Archive Resource',
            'target_table'  => 'resources',
            'target_id'     => (string)$resourceId,
            'description'   => "Archived resource ID {$resourceId}",
            'actor_user_id' => $userId,
            'resource_id'   => $resourceId
        ]);

        respond([
            'status' => 'success',
            'message' => 'Resource archived successfully.'
        ]);
    }

    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Resources API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
