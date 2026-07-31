<?php
// Prevent session lock issues during long DB queries
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Dynamic CORS Policy
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
require_once __DIR__ . '/../../src/Services/AuditLogger.php';

// Response Helper
function respond(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    // 1. Authentication Check
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session. Please sign in.'
        ], 401);
    }

    // 2. Fetch user granted actions for Action Management resource
    $userPermRows = !empty($_SESSION['role_id']) ? ($db->query("
        SELECT DISTINCT UPPER(a.action_name) AS action_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.permission_id
        JOIN actions a ON p.action_id = a.action_id
        JOIN resources res ON p.resource_id = res.resource_id
        WHERE rp.role_id = :rid AND (LOWER(res.resource_name) LIKE '%action%' OR LOWER(res.resource_name) LIKE '%permission%')
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

    $canCreateAction = $isSuperAdmin || in_array('CREATE', $userGrantedActions);
    $canEditAction   = $isSuperAdmin || in_array('EDIT', $userGrantedActions);
    $canDeleteAction = $isSuperAdmin || in_array('DELETE', $userGrantedActions);

    if ($method === 'GET') {
        $actions = $db->query("SELECT action_id, action_name, description, status, created_at, updated_at FROM actions ORDER BY action_id ASC");
        respond([
            'status' => 'success',
            'data' => $actions ?: [],
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
        if (!$canCreateAction) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to create system action verbs.'
            ], 403);
        }

        $actionName = trim($data['action_name'] ?? '');
        $description = trim($data['description'] ?? '');
        $status = in_array($data['status'] ?? '', ['Active', 'Inactive', 'Archived']) ? $data['status'] : 'Active';

        if (empty($actionName)) {
            respond([
                'status' => 'error',
                'message' => 'Action Name is required.'
            ], 400);
        }

        $existing = $db->query("SELECT action_id FROM actions WHERE LOWER(action_name) = LOWER(:aname)", ['aname' => $actionName]);
        if (!empty($existing)) {
            respond([
                'status' => 'error',
                'message' => 'An action verb with this name already exists.'
            ], 400);
        }

        $newId = $db->insert('actions', [
            'action_name' => $actionName,
            'description' => $description ?: null,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $newActionRows = $db->query("SELECT * FROM actions WHERE action_id = :id", ['id' => $newId]);
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Create Action',
            'target_table'  => 'actions',
            'target_id'     => (string)$newId,
            'description'   => "Created action verb \"{$actionName}\"",
            'actor_user_id' => $userId,
            'old_data'      => null,
            'new_data'      => !empty($newActionRows) ? $newActionRows[0] : null
        ]);

        respond([
            'status' => 'success',
            'message' => "Action verb \"{$actionName}\" created successfully.",
            'action_id' => $newId
        ], 201);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if (!$canEditAction) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to modify or edit system action verbs.'
            ], 403);
        }

        $actionId = filter_var($data['action_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$actionId) {
            respond([
                'status' => 'error',
                'message' => 'Valid action_id is required.'
            ], 400);
        }

        $updatePayload = ['updated_at' => date('Y-m-d H:i:s')];

        if (isset($data['action_name'])) $updatePayload['action_name'] = trim($data['action_name']);
        if (isset($data['description'])) $updatePayload['description'] = trim($data['description']);
        if (isset($data['status']) && in_array($data['status'], ['Active', 'Inactive', 'Archived'])) $updatePayload['status'] = $data['status'];

        $oldActionRows = $db->query("SELECT * FROM actions WHERE action_id = :id", ['id' => $actionId]);
        $oldAction = !empty($oldActionRows) ? $oldActionRows[0] : null;

        $db->update('actions', $updatePayload, ['action_id' => $actionId]);

        $newActionRows = $db->query("SELECT * FROM actions WHERE action_id = :id", ['id' => $actionId]);
        $newAction = !empty($newActionRows) ? $newActionRows[0] : null;

        \App\Services\AuditLogger::logMutation([
            'action'        => 'Update Action',
            'target_table'  => 'actions',
            'target_id'     => (string)$actionId,
            'description'   => "Updated action verb ID {$actionId}",
            'actor_user_id' => $userId,
            'old_data'      => $oldAction,
            'new_data'      => $newAction
        ]);

        respond([
            'status' => 'success',
            'message' => 'Action verb updated successfully.'
        ]);
    }

    if ($method === 'DELETE') {
        if (!$canDeleteAction) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to delete system action verbs.'
            ], 403);
        }

        $actionId = filter_var($_GET['action_id'] ?? $data['action_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$actionId) {
            respond([
                'status' => 'error',
                'message' => 'Valid action_id is required.'
            ], 400);
        }

        $oldActionRows = $db->query("SELECT * FROM actions WHERE action_id = :id", ['id' => $actionId]);
        $oldAction = !empty($oldActionRows) ? $oldActionRows[0] : null;

        $db->update('actions', [
            'status' => 'Archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['action_id' => $actionId]);

        $newActionRows = $db->query("SELECT * FROM actions WHERE action_id = :id", ['id' => $actionId]);
        $newAction = !empty($newActionRows) ? $newActionRows[0] : null;

        \App\Services\AuditLogger::logMutation([
            'action'        => 'Archive Action',
            'target_table'  => 'actions',
            'target_id'     => (string)$actionId,
            'description'   => "Archived action verb ID {$actionId}",
            'actor_user_id' => $userId,
            'old_data'      => $oldAction,
            'new_data'      => $newAction
        ]);

        respond([
            'status' => 'success',
            'message' => 'Action verb archived successfully.'
        ]);
    }

    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Actions API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
