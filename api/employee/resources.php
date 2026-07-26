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
        $sql = "SELECT r.*, m.module_name, m.description AS module_desc
                FROM resources r
                LEFT JOIN modules m ON r.module_id = m.module_id
                ORDER BY r.resource_id ASC";
        $resourcesRaw = $db->query($sql) ?: [];

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

        $modules = $db->query("SELECT module_id, module_name FROM modules WHERE status = 'Active' ORDER BY module_name ASC") ?: [];

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

        // Audit Trail
        try {
            $db->insert('audit_logs', [
                'actor_user_id' => $userId,
                'action' => 'Create Resource',
                'target_table' => 'resources',
                'target_id' => (string)$newId,
                'description' => "Created resource \"{$resourceName}\" under module ID {$moduleId}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'request_method' => 'POST',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/resources.php',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'status' => 'Success'
            ]);
        } catch (Throwable $auditEx) {}

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
        if (isset($data['status']) && in_array($data['status'], ['Active', 'Inactive'])) $updatePayload['status'] = $data['status'];

        $db->update('resources', $updatePayload, ['resource_id' => $resourceId]);

        // Audit Trail
        try {
            $db->insert('audit_logs', [
                'actor_user_id' => $userId,
                'action' => 'Update Resource',
                'target_table' => 'resources',
                'target_id' => (string)$resourceId,
                'description' => "Updated resource ID {$resourceId}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'request_method' => $method,
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/resources.php',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'status' => 'Success'
            ]);
        } catch (Throwable $auditEx) {}

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

        $db->delete('resources', ['resource_id' => $resourceId]);

        // Audit Trail
        try {
            $db->insert('audit_logs', [
                'actor_user_id' => $userId,
                'action' => 'Delete Resource',
                'target_table' => 'resources',
                'target_id' => (string)$resourceId,
                'description' => "Deleted resource ID {$resourceId}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'request_method' => 'DELETE',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/resources.php',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'status' => 'Success'
            ]);
        } catch (Throwable $auditEx) {}

        respond([
            'status' => 'success',
            'message' => 'Resource deleted successfully.'
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
