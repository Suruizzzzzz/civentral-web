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
require_once __DIR__ . '/../../config/mailer.php';
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

    // 3. Authorization & Scope Check
    $currentUserInfo = $db->query("
        SELECT u.user_id, u.role_id, r.role_prefix, r.is_global_access, r.is_superadmin, p.department_id, d.department_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.role_id
        LEFT JOIN positions p ON u.position_id = p.position_id
        LEFT JOIN departments d ON p.department_id = d.department_id
        WHERE u.user_id = :uid
    ", ['uid' => $userId]);

    $isAuthorized = false;
    $isSuperAdminOrGlobal = false;
    $userDepartmentId = null;
    $userDepartmentName = null;

    if (!empty($currentUserInfo)) {
        $userRow = $currentUserInfo[0];
        $userDepartmentId = !empty($userRow['department_id']) ? (int)$userRow['department_id'] : null;
        $userDepartmentName = $userRow['department_name'] ?? null;

        if (!$userDepartmentId && !empty($_SESSION['department_id'])) {
            $userDepartmentId = (int)$_SESSION['department_id'];
        }

        if (!$userDepartmentId) {
            $sessionRes = $_SESSION['granted_resources'] ?? [];
            if (is_array($sessionRes)) {
                foreach ($sessionRes as $gr) {
                    if (strpos(strtolower($gr), 'scholarship') !== false || strpos(strtolower($gr), 'education') !== false) {
                        $matchedDept = $db->query("SELECT department_id, department_name FROM departments WHERE LOWER(department_name) LIKE '%scholarship%' OR LOWER(department_name) LIKE '%education%' LIMIT 1");
                        if (!empty($matchedDept)) {
                            $userDepartmentId = (int)$matchedDept[0]['department_id'];
                            $userDepartmentName = $matchedDept[0]['department_name'];
                            break;
                        }
                    }
                }
            }
        }

        $rolePrefix = strtoupper($userRow['role_prefix'] ?? '');
        $roleName = strtolower($userRow['role_name'] ?? '');
        $isSuperAdmin = (!empty($userRow['is_superadmin']) && intval($userRow['is_superadmin']) === 1) || in_array($rolePrefix, ['SA', 'SADM']) || $roleName === 'super administrator' || $roleName === 'superadmin';

        if ($isSuperAdmin) {
            $isAuthorized = true;
            $isSuperAdminOrGlobal = true;
        } else {
            $isAuthorized = true;
            $isSuperAdminOrGlobal = false;
        }
    }

    $userPermRows = !empty($userRow['role_id']) ? ($db->query("
        SELECT DISTINCT UPPER(a.action_name) AS action_name, LOWER(res.resource_name) AS resource_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.permission_id
        JOIN actions a ON p.action_id = a.action_id
        JOIN resources res ON p.resource_id = res.resource_id
        WHERE rp.role_id = :rid
    ", ['rid' => $userRow['role_id']]) ?: []) : [];

    $userGrantedActions = [];
    $userGrantedResources = [];
    foreach ($userPermRows as $pr) {
        if (!empty($pr['action_name'])) $userGrantedActions[] = strtoupper($pr['action_name']);
        if (!empty($pr['resource_name'])) $userGrantedResources[] = strtolower(trim($pr['resource_name']));
    }
    $userGrantedActions = array_values(array_unique($userGrantedActions));
    $userGrantedResources = array_values(array_unique($userGrantedResources));

    if ($userDepartmentId && !$userDepartmentName) {
        $deptRow = $db->query("SELECT department_name FROM departments WHERE department_id = :did", ['did' => $userDepartmentId]);
        if (!empty($deptRow)) {
            $userDepartmentName = $deptRow[0]['department_name'];
        }
    }

    // Helper: Fetch roles for a given department
    $getDepartmentRoles = function($deptId) use ($db, $isSuperAdminOrGlobal) {
        if (!$deptId) {
            if ($isSuperAdminOrGlobal) {
                return $db->query("SELECT role_id, role_name, role_prefix, is_superadmin, is_global_access FROM roles WHERE status = 'Active' ORDER BY role_name ASC") ?: [];
            } else {
                return $db->query("SELECT role_id, role_name, role_prefix, is_superadmin, is_global_access FROM roles WHERE status = 'Active' AND (is_superadmin IS NULL OR is_superadmin = 0) AND (is_global_access IS NULL OR is_global_access = 0) AND UPPER(role_prefix) NOT IN ('SA', 'SADM') ORDER BY role_name ASC") ?: [];
            }
        }

        $deptInfo = $db->query("SELECT department_name, department_code FROM departments WHERE department_id = :did", ['did' => $deptId]);
        if (empty($deptInfo)) return [];

        $dName = strtolower($deptInfo[0]['department_name']);
        $dCode = strtoupper($deptInfo[0]['department_code']);

        $stopWords = ['department', 'office', 'bureau', 'division', 'service', 'services', 'and', '&', 'of', 'management'];
        $rawTerms = array_values(array_filter(explode(' ', preg_replace('/[^a-z0-9 ]/', '', $dName))));
        $terms = array_values(array_filter($rawTerms, function($t) use ($stopWords) {
            return strlen($t) > 2 && !in_array($t, $stopWords);
        }));

        $permWhereConds = [];
        $roleWhereConds = [];
        $params = [
            'did1' => $deptId,
            'did2' => $deptId,
            'did3' => $deptId,
            'codeKw' => '%' . $dCode . '%'
        ];

        $paramIdx = 0;
        foreach ($terms as $t) {
            $termVal = '%' . $t . '%';
            $p1 = "p_" . ($paramIdx++);
            $p2 = "p_" . ($paramIdx++);
            $p3 = "p_" . ($paramIdx++);
            $p4 = "p_" . ($paramIdx++);
            $permWhereConds[] = "LOWER(m.module_name) LIKE :{$p1} OR LOWER(res.resource_name) LIKE :{$p2} OR LOWER(r.role_name) LIKE :{$p3} OR LOWER(r.description) LIKE :{$p4}";
            $params[$p1] = $termVal;
            $params[$p2] = $termVal;
            $params[$p3] = $termVal;
            $params[$p4] = $termVal;

            $r1 = "r_" . ($paramIdx++);
            $r2 = "r_" . ($paramIdx++);
            $roleWhereConds[] = "LOWER(r.role_name) LIKE :{$r1} OR LOWER(r.description) LIKE :{$r2}";
            $params[$r1] = $termVal;
            $params[$r2] = $termVal;
        }

        $permWhereClause = !empty($permWhereConds) ? '(' . implode(' OR ', $permWhereConds) . ')' : "1=0";
        $roleWhereClause = !empty($roleWhereConds) ? '(' . implode(' OR ', $roleWhereConds) . ')' : "1=0";

        $sql = "
            SELECT DISTINCT r.role_id, r.role_name, r.role_prefix, r.department_id, r.is_superadmin, r.is_global_access
            FROM roles r
            WHERE r.status = 'Active'
              " . (!$isSuperAdminOrGlobal ? "AND (r.is_superadmin IS NULL OR r.is_superadmin = 0) AND (r.is_global_access IS NULL OR r.is_global_access = 0) AND UPPER(r.role_prefix) NOT IN ('SA', 'SADM')" : "") . "
              AND (
                  r.department_id = :did1
                  OR r.role_id IN (
                      SELECT rda.role_id
                      FROM role_department_access rda
                      WHERE rda.department_id = :did2
                  )
                  OR r.role_id IN (
                      SELECT rp.role_id
                      FROM role_permissions rp
                      JOIN permissions p ON rp.permission_id = p.permission_id
                      JOIN resources res ON p.resource_id = res.resource_id
                      JOIN modules m ON res.module_id = m.module_id
                      WHERE {$permWhereClause}
                  )
                  OR r.role_id IN (
                      SELECT u.role_id
                      FROM users u
                      JOIN positions pos ON u.position_id = pos.position_id
                      WHERE pos.department_id = :did3
                  )
                  OR {$roleWhereClause}
                  OR UPPER(r.role_prefix) LIKE :codeKw
              )
            ORDER BY r.role_name ASC
        ";

        $deptRoles = $db->query($sql, $params) ?: [];

        if (empty($deptRoles)) {
            $fallbackSql = "
                SELECT role_id, role_name, role_prefix, is_superadmin, is_global_access
                FROM roles
                WHERE status = 'Active'
                  " . (!$isSuperAdminOrGlobal ? "AND (is_superadmin IS NULL OR is_superadmin = 0) AND (is_global_access IS NULL OR is_global_access = 0) AND UPPER(role_prefix) NOT IN ('SA', 'SADM')" : "") . "
                ORDER BY role_name ASC
            ";
            $deptRoles = $db->query($fallbackSql) ?: [];
        }

        return $deptRoles;
    };

    // A. GET Request Handler
    if ($method === 'GET') {
        $action = trim($_GET['action'] ?? '');

        // Action: Get Roles by Department ID
        if ($action === 'get_roles_by_dept') {
            $targetDeptId = filter_var($_GET['department_id'] ?? null, FILTER_VALIDATE_INT);
            $rolesList = $getDepartmentRoles($targetDeptId ?: $userDepartmentId);
            respond([
                'status' => 'success',
                'roles' => $rolesList
            ]);
        }

        // Action: Generate Employee ID Preview
        if ($action === 'generate_emp_id') {
            $roleId = filter_var($_GET['role_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$roleId) {
                respond(['status' => 'error', 'message' => 'role_id is required.'], 400);
            }

            $roles = $db->select('roles', ['role_id' => $roleId]);
            $prefix = (!empty($roles) && !empty($roles[0]['role_prefix'])) ? strtoupper(trim($roles[0]['role_prefix'])) : 'STF';
            $year = date('Y');

            // Count existing users with matching prefix
            $pattern = "{$prefix}-{$year}-%";
            $countRows = $db->query("SELECT COUNT(*) AS total FROM users WHERE employee_id LIKE :pattern", ['pattern' => $pattern]);
            $sequence = intval($countRows[0]['total'] ?? 0) + 1;
            $newEmpId = sprintf("%s-%s-%03d", $prefix, $year, $sequence);

            respond([
                'status' => 'success',
                'employee_id' => $newEmpId,
                'role_prefix' => $prefix
            ]);
        }

        // Fetch Users with JOINs & Department Scope
        $sql = "SELECT u.*, 
                       r.role_name, r.role_prefix, r.is_global_access,
                       p.position_name, p.department_id,
                       d.department_name, d.department_code
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.role_id
                LEFT JOIN positions p ON u.position_id = p.position_id
                LEFT JOIN departments d ON p.department_id = d.department_id";

        $queryParams = [];
        if (!$isSuperAdminOrGlobal && $userDepartmentId) {
            $sql .= " WHERE p.department_id = :dept_id";
            $queryParams['dept_id'] = $userDepartmentId;
        }

        $sql .= " ORDER BY u.user_id DESC";

        $rawUsers = $db->query($sql, $queryParams) ?: [];

        $users = [];
        foreach ($rawUsers as $row) {
            $middleNamePart = !empty($row['middle_name']) ? trim($row['middle_name']) . ' ' : '';
            $fullName = trim(($row['first_name'] ?? '') . ' ' . $middleNamePart . ($row['last_name'] ?? ''));

            $users[] = [
                'user_id' => $row['user_id'],
                'employee_id' => $row['employee_id'],
                'first_name' => $row['first_name'],
                'middle_name' => $row['middle_name'],
                'last_name' => $row['last_name'],
                'full_name' => $fullName,
                'email' => $row['email'],
                'mobile_number' => $row['mobile_number'],
                'role_id' => $row['role_id'],
                'position_id' => $row['position_id'],
                'status' => $row['status'],
                'is_first_login' => !empty($row['is_first_login']),
                'last_login' => $row['last_login'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'roles' => $row['role_id'] ? [
                    'role_id' => $row['role_id'],
                    'role_name' => $row['role_name'],
                    'role_prefix' => $row['role_prefix'],
                    'is_global_access' => $row['is_global_access']
                ] : null,
                'positions' => $row['position_id'] ? [
                    'position_id' => $row['position_id'],
                    'position_name' => $row['position_name'],
                    'departments' => [
                        'department_id' => $row['department_id'],
                        'department_name' => $row['department_name'],
                        'department_code' => $row['department_code']
                    ]
                ] : null
            ];
        }

        $initialTargetDept = $isSuperAdminOrGlobal ? null : $userDepartmentId;
        $roles = $getDepartmentRoles($initialTargetDept);

        $deptSql = "SELECT department_id, department_code, department_name FROM departments WHERE status = 'Active' ORDER BY department_name ASC";
        $departments = $db->query($deptSql) ?: [];

        $posSql = "SELECT position_id, department_id, position_name FROM positions WHERE status = 'Active'";
        $posParams = [];
        if (!$isSuperAdminOrGlobal && $userDepartmentId) {
            $posSql .= " AND department_id = :did";
            $posParams['did'] = $userDepartmentId;
        }
        $posSql .= " ORDER BY position_name ASC";
        $positions = $db->query($posSql, $posParams) ?: [];

        respond([
            'status' => 'success',
            'data' => $users,
            'roles' => $roles,
            'departments' => $departments,
            'positions' => $positions,
            'current_user' => [
                'user_id' => (int)$userId,
                'department_id' => $userDepartmentId,
                'department_name' => $userDepartmentName,
                'is_superadmin' => $isSuperAdminOrGlobal,
                'granted_actions' => $userGrantedActions,
                'granted_resources' => $userGrantedResources
            ]
        ]);
    }

    // Action Permission Checks
    $canCreate = $isSuperAdminOrGlobal || in_array('CREATE', $userGrantedActions);
    $canEdit   = $isSuperAdminOrGlobal || in_array('EDIT', $userGrantedActions);
    $canDelete = $isSuperAdminOrGlobal || in_array('DELETE', $userGrantedActions);

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?? $_POST;

    // B. POST Request Handler (Create User)
    if ($method === 'POST') {
        if (!$canCreate) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to create user accounts.'
            ], 403);
        }
        $firstName = trim($data['first_name'] ?? '');
        $middleName = trim($data['middle_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $mobileNumber = trim($data['mobile_number'] ?? '');
        $password = trim($data['password'] ?? '');
        $roleId = filter_var($data['role_id'] ?? null, FILTER_VALIDATE_INT);
        $departmentId = filter_var($data['department_id'] ?? null, FILTER_VALIDATE_INT);
        $positionName = trim($data['position_name'] ?? '');
        $positionId = filter_var($data['position_id'] ?? null, FILTER_VALIDATE_INT);
        $customEmployeeId = strtoupper(trim($data['employee_id'] ?? ''));
        $status = in_array($data['status'] ?? '', ['Active', 'Inactive', 'Locked', 'Archived']) ? $data['status'] : 'Active';

        // 1. Auto-generate temporary password if not provided by form
        if (empty($password)) {
            $password = 'Civentral@' . rand(1000, 9999);
        }

        // 2. Resolve or create position_id if position_name is passed
        if (!$positionId && !empty($positionName) && $departmentId) {
            $existingPos = $db->query(
                "SELECT position_id FROM positions WHERE department_id = :dept_id AND LOWER(position_name) = LOWER(:pos_name) LIMIT 1",
                ['dept_id' => $departmentId, 'pos_name' => $positionName]
            );
            if (!empty($existingPos)) {
                $positionId = $existingPos[0]['position_id'];
            } else {
                $positionId = $db->insert('positions', [
                    'department_id' => $departmentId,
                    'position_name' => $positionName,
                    'status' => 'Active'
                ]);
            }
        }

        if (empty($firstName) || empty($lastName) || empty($email) || !$roleId || !$positionId) {
            respond([
                'status' => 'error',
                'message' => 'First Name, Last Name, Email, Role, and Position are required.'
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond([
                'status' => 'error',
                'message' => 'Please enter a valid email address.'
            ], 400);
        }

        // Department Scope Security Checks
        if (!$isSuperAdminOrGlobal) {
            if ($departmentId !== $userDepartmentId) {
                respond([
                    'status' => 'error',
                    'message' => 'Forbidden. Department Administrators can only create users within their own department.'
                ], 403);
            }

            $targetRoles = $db->select('roles', ['role_id' => $roleId]);
            if (!empty($targetRoles)) {
                $targetRole = $targetRoles[0];
                if (!empty($targetRole['is_superadmin']) || !empty($targetRole['is_global_access']) || in_array(strtoupper($targetRole['role_prefix'] ?? ''), ['SA', 'SADM'])) {
                    respond([
                        'status' => 'error',
                        'message' => 'Forbidden. Department Administrators cannot assign Super Admin or global access roles.'
                    ], 403);
                }
            }
        } else {
            $targetRoles = $db->select('roles', ['role_id' => $roleId]);
            if (!empty($targetRoles)) {
                $targetRole = $targetRoles[0];
                if (!empty($targetRole['is_superadmin']) || intval($targetRole['is_superadmin'] ?? 0) === 1) {
                    respond([
                        'status' => 'error',
                        'message' => 'Superadmin accounts must be provisioned via System Security Console.'
                    ], 403);
                }
            }
        }

        // Email Uniqueness Check
        $dupEmail = $db->query("SELECT user_id FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
        if (!empty($dupEmail)) {
            respond([
                'status' => 'error',
                'message' => 'This email address is already registered.'
            ], 400);
        }

        // Generate or Validate Employee ID
        if (!empty($customEmployeeId)) {
            $dupEmp = $db->query("SELECT user_id FROM users WHERE UPPER(employee_id) = UPPER(:empid) LIMIT 1", ['empid' => $customEmployeeId]);
            if (!empty($dupEmp)) {
                respond([
                    'status' => 'error',
                    'message' => 'This Employee ID is already assigned to another user.'
                ], 400);
            }
            $finalEmpId = $customEmployeeId;
        } else {
            $roles = $db->select('roles', ['role_id' => $roleId]);
            $prefix = (!empty($roles) && !empty($roles[0]['role_prefix'])) ? strtoupper(trim($roles[0]['role_prefix'])) : 'STF';
            $year = date('Y');

            $pattern = "{$prefix}-{$year}-%";
            $countRows = $db->query("SELECT COUNT(*) AS total FROM users WHERE employee_id LIKE :pattern", ['pattern' => $pattern]);
            $sequence = intval($countRows[0]['total'] ?? 0) + 1;
            $finalEmpId = sprintf("%s-%s-%03d", $prefix, $year, $sequence);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $insertPayload = [
            'employee_id' => $finalEmpId,
            'role_id' => $roleId,
            'position_id' => $positionId,
            'first_name' => $firstName,
            'middle_name' => $middleName ?: null,
            'last_name' => $lastName,
            'mobile_number' => $mobileNumber ?: null,
            'email' => $email,
            'password' => $hashedPassword,
            'status' => $status,
            'is_first_login' => 1,
            'email_verified' => 1,
            'mobile_verified' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $newUserId = $db->insert('users', $insertPayload);

        $newUserRow = $db->select('users', ['user_id' => $newUserId])[0] ?? $insertPayload;

        // Audit Trail Log
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Create User Account',
            'target_table'  => 'users',
            'target_id'     => (string)$newUserId,
            'actor_user_id' => $userId,
            'old_data'      => null,
            'new_data'      => $newUserRow
        ]);

        // Dispatch Welcome & Credentials Email to User
        $fullName = trim("{$firstName} {$lastName}");
        $emailSubject = "Welcome to CIVENTRAL - Account Credentials";
        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                <div style='text-align: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;'>
                    <h2 style='color: #0f172a; margin: 0; font-size: 20px; font-weight: 900; tracking-tight;'>CIVENTRAL PORTAL</h2>
                    <p style='color: #86B6F6; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 4px;'>Caloocan Municipal Management System</p>
                </div>
                <p style='color: #334155; font-size: 14px;'>Hello <strong>{$fullName}</strong>,</p>
                <p style='color: #475569; font-size: 14px; line-height: 1.5;'>Your official CIVENTRAL system user account has been successfully generated. Below are your assigned Employee ID and login credentials:</p>
                
                <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>
                        <tr>
                            <td style='padding: 6px 0; color: #64748b; font-weight: bold;'>Assigned Employee ID:</td>
                            <td style='padding: 6px 0; color: #0f172a; font-family: monospace; font-weight: bold; text-align: right;'>{$finalEmpId}</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #64748b; font-weight: bold;'>Registered Email:</td>
                            <td style='padding: 6px 0; color: #0f172a; font-weight: bold; text-align: right;'>{$email}</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #64748b; font-weight: bold;'>Temporary Password:</td>
                            <td style='padding: 6px 0; color: #176B87; font-family: monospace; font-weight: 900; font-size: 15px; text-align: right;'>{$password}</td>
                        </tr>
                    </table>
                </div>

                <p style='color: #475569; font-size: 12px; line-height: 1.5;'>Please log in to the portal using these credentials. Upon first sign-in, you will be prompted to update your password for security compliance.</p>
                <div style='text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #f1f5f9;'>
                    <p style='color: #94a3b8; font-size: 11px;'>This is an automated system notification. Please do not reply directly to this email.</p>
                </div>
            </div>
        ";

        try {
            sendSystemEmail($email, $fullName, $emailSubject, $emailBody);
        } catch (Throwable $mailEx) {
            error_log("Failed sending account creation email: " . $mailEx->getMessage());
        }

        respond([
            'status' => 'success',
            'message' => "User account for {$firstName} {$lastName} created successfully! Assigned Employee ID: {$finalEmpId}",
            'user_id' => $newUserId,
            'employee_id' => $finalEmpId,
            'user_name' => $fullName,
            'email' => $email,
            'temp_password' => $password
        ], 201);
    }

    // C. PUT / PATCH Request Handler (Update User)
    if ($method === 'PUT' || $method === 'PATCH') {
        if (!$canEdit) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to modify or edit user accounts.'
            ], 403);
        }

        $targetUserId = filter_var($data['user_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$targetUserId) {
            respond([
                'status' => 'error',
                'message' => 'Valid user_id parameter is required.'
            ], 400);
        }

        $targetUsers = $db->select('users', ['user_id' => $targetUserId]);
        if (empty($targetUsers)) {
            respond([
                'status' => 'error',
                'message' => 'Target user account not found.'
            ], 404);
        }

        $targetUser = $targetUsers[0];

        // Safeguard Superadmin Account (SA-2026-001)
        if (strtoupper($targetUser['employee_id'] ?? '') === 'SA-2026-001' && isset($data['status']) && $data['status'] !== 'Active') {
            respond([
                'status' => 'error',
                'message' => 'The main Super Administrator account cannot be deactivated.'
            ], 403);
        }

        $updatePayload = ['updated_at' => date('Y-m-d H:i:s')];

        if (isset($data['first_name'])) $updatePayload['first_name'] = trim($data['first_name']);
        if (isset($data['middle_name'])) $updatePayload['middle_name'] = trim($data['middle_name']) ?: null;
        if (isset($data['last_name'])) $updatePayload['last_name'] = trim($data['last_name']);
        if (isset($data['mobile_number'])) $updatePayload['mobile_number'] = trim($data['mobile_number']) ?: null;
        if (isset($data['role_id'])) $updatePayload['role_id'] = filter_var($data['role_id'], FILTER_VALIDATE_INT);
        if (isset($data['position_id'])) $updatePayload['position_id'] = filter_var($data['position_id'], FILTER_VALIDATE_INT);
        if (isset($data['status']) && in_array($data['status'], ['Active', 'Inactive', 'Locked', 'Archived'])) {
            $updatePayload['status'] = $data['status'];
            if ($data['status'] === 'Active') {
                $updatePayload['failed_attempts'] = 0;
            }
        }

        if (!empty($data['password'])) {
            $updatePayload['password'] = password_hash(trim($data['password']), PASSWORD_BCRYPT);
            $updatePayload['password_changed_at'] = date('Y-m-d H:i:s');
        }

        $db->update('users', $updatePayload, ['user_id' => $targetUserId]);

        $updatedUser = $db->select('users', ['user_id' => $targetUserId])[0] ?? $updatePayload;

        // Audit Trail
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Update User Account',
            'target_table'  => 'users',
            'target_id'     => (string)$targetUserId,
            'actor_user_id' => $userId,
            'old_data'      => $targetUser,
            'new_data'      => $updatedUser
        ]);

        respond([
            'status' => 'success',
            'message' => "User account for {$targetUser['employee_id']} updated successfully."
        ]);
    }

    // D. DELETE Request Handler (Deactivate User)
    if ($method === 'DELETE') {
        if (!$canDelete) {
            respond([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to delete or archive user accounts.'
            ], 403);
        }

        $targetUserId = filter_var($_GET['user_id'] ?? $data['user_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$targetUserId) {
            respond([
                'status' => 'error',
                'message' => 'Valid user_id parameter is required.'
            ], 400);
        }

        // Prevent self-deactivation
        if ($targetUserId === intval($userId)) {
            respond([
                'status' => 'error',
                'message' => 'You cannot deactivate your own active session account.'
            ], 400);
        }

        $targetUsers = $db->select('users', ['user_id' => $targetUserId]);
        if (empty($targetUsers)) {
            respond([
                'status' => 'error',
                'message' => 'Target user account not found.'
            ], 404);
        }

        $targetUser = $targetUsers[0];

        // Safeguard Superadmin Account (SA-2026-001)
        if (strtoupper($targetUser['employee_id'] ?? '') === 'SA-2026-001') {
            respond([
                'status' => 'error',
                'message' => 'The main Super Administrator account cannot be deleted or deactivated.'
            ], 403);
        }

        // Soft Delete (Set status to Archived)
        $db->update('users', [
            'status' => 'Archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['user_id' => $targetUserId]);

        $archivedUser = $db->select('users', ['user_id' => $targetUserId])[0] ?? $targetUser;

        // Audit Trail
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Archive User Account',
            'target_table'  => 'users',
            'target_id'     => (string)$targetUserId,
            'actor_user_id' => $userId,
            'old_data'      => $targetUser,
            'new_data'      => $archivedUser
        ]);

        respond([
            'status' => 'success',
            'message' => "User account {$targetUser['employee_id']} has been archived successfully."
        ]);
    }

    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Users API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
