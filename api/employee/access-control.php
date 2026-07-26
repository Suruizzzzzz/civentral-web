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
            'message' => 'Unauthorized session. Please sign in to view or manage access control.'
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

    if (!$isAuthorized) {
        respond([
            'status' => 'error',
            'message' => 'Forbidden. You do not have administrative privileges to manage access boundaries.'
        ], 403);
    }

    // 4. GET Handler
    if ($method === 'GET') {
        $roles = $db->query("SELECT role_id, role_name, role_prefix, is_global_access, description, status, is_system_role FROM roles ORDER BY role_id ASC");
        $departments = $db->query("SELECT department_id, department_code, department_name, description, status FROM departments WHERE status = 'Active' ORDER BY department_id ASC");
        $roleAccessMap = $db->query("SELECT access_id, role_id, department_id, created_at FROM role_department_access");

        respond([
            'status' => 'success',
            'roles' => $roles ?: [],
            'departments' => $departments ?: [],
            'role_department_access' => $roleAccessMap ?: []
        ]);
    }

    // 5. POST Handler
    if ($method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true) ?? [];

        $roleId = filter_var($data['role_id'] ?? null, FILTER_VALIDATE_INT);
        $isGlobalAccess = !empty($data['is_global_access']);
        
        $departmentIds = array_unique(array_filter(
            array_map('intval', is_array($data['department_ids'] ?? null) ? $data['department_ids'] : []),
            fn($id) => $id > 0
        ));

        if (!$roleId) {
            respond([
                'status' => 'error',
                'message' => 'Valid role_id parameter is required.'
            ], 400);
        }

        // Target Role Verification
        $targetRoles = $db->select('roles', ['role_id' => $roleId]);
        if (empty($targetRoles)) {
            respond([
                'status' => 'error',
                'message' => 'Target role not found.'
            ], 404);
        }

        $targetRole = $targetRoles[0];

        // Super Admin protection
        if (strtoupper($targetRole['role_prefix'] ?? '') === 'SA' || !empty($targetRole['is_system_role'])) {
            $isGlobalAccess = true;
        }

        // Database Transaction
        $pdo = $db->getPdo();
        $pdo->beginTransaction();

        try {
            // A. Update Role
            $db->update('roles', [
                'is_global_access' => $isGlobalAccess ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['role_id' => $roleId]);

            // B. Clear Old Access
            $db->delete('role_department_access', ['role_id' => $roleId]);

            // C. Insert New Access
            if (!$isGlobalAccess && !empty($departmentIds)) {
                $placeholders = implode(',', array_fill(0, count($departmentIds), '?'));
                $validDepts = $db->query(
                    "SELECT department_id FROM departments WHERE department_id IN ({$placeholders}) AND status = 'Active'", 
                    $departmentIds
                );
                
                $validDeptIds = array_column($validDepts, 'department_id');

                foreach ($validDeptIds as $deptId) {
                    $db->insert('role_department_access', [
                        'role_id' => $roleId,
                        'department_id' => $deptId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // D. Non-blocking Audit Logging
            try {
                $db->insert('audit_logs', [
                    'actor_user_id' => $userId,
                    'action' => 'Update Access Control Matrix',
                    'target_table' => 'roles',
                    'target_id' => (string)$roleId,
                    'description' => "Updated department access boundary for role \"{$targetRole['role_name']}\" (Global Access: " . ($isGlobalAccess ? 'YES' : 'NO') . ")",
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    'request_method' => 'POST',
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/access-control.php',
                    'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    'status' => 'Success'
                ]);
            } catch (Throwable $auditEx) {
                error_log("Audit log failed: " . $auditEx->getMessage());
            }

            $pdo->commit();

            respond([
                'status' => 'success',
                'message' => "Access control boundary for role \"{$targetRole['role_name']}\" saved successfully."
            ]);

        } catch (Throwable $txEx) {
            $pdo->rollBack();
            throw $txEx;
        }
    }

    // Method Not Allowed
    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Access Control API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
