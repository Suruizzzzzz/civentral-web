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

header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS');
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
    $employeeId = $_SESSION['employee_id'] ?? null;

    if (!$userId && !$employeeId) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session. Please sign in to view or update your profile.'
        ], 401);
    }

    if ($method === 'GET') {
        $sql = "SELECT u.*, 
                       r.role_name, r.role_prefix, r.is_global_access,
                       p.position_name, p.department_id,
                       d.department_name, d.department_code
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.role_id
                LEFT JOIN positions p ON u.position_id = p.position_id
                LEFT JOIN departments d ON p.department_id = d.department_id
                WHERE u.user_id = :user_id OR (u.employee_id = :emp_id AND :emp_id_check IS NOT NULL)
                LIMIT 1";

        $users = $db->query($sql, [
            'user_id' => $userId ?: 0,
            'emp_id' => $employeeId ?: '',
            'emp_id_check' => $employeeId
        ]);

        if (empty($users)) {
            respond([
                'status' => 'error',
                'message' => 'User account record not found.'
            ], 404);
        }

        $user = $users[0];

        $middleNamePart = !empty($user['middle_name']) ? trim($user['middle_name']) . ' ' : '';
        $fullName = trim(($user['first_name'] ?? '') . ' ' . $middleNamePart . ($user['last_name'] ?? ''));

        respond([
            'status' => 'success',
            'data' => [
                'user_id' => intval($user['user_id']),
                'employee_id' => $user['employee_id'],
                'first_name' => $user['first_name'] ?? '',
                'middle_name' => $user['middle_name'] ?? '',
                'last_name' => $user['last_name'] ?? '',
                'full_name' => $fullName,
                'email' => $user['email'] ?? '',
                'mobile_number' => $user['mobile_number'] ?? '',
                'role_name' => $user['role_name'] ?? 'Staff',
                'position_name' => $user['position_name'] ?? 'Staff Member',
                'department_name' => $user['department_name'] ?? 'General Office',
                'department_code' => $user['department_code'] ?? 'GEN',
                'status' => $user['status'] ?? 'Active',
                'profile_picture' => $user['profile_picture'] ?? 'default-avatar.png',
                'last_login' => $user['last_login']
            ]
        ]);
    }

    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true) ?? $_POST;

        $targetUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$targetUserId) {
            respond([
                'status' => 'error',
                'message' => 'Session expired. Please sign in again.'
            ], 401);
        }

        // 1. Fetch current user record from database as baseline
        $existingUsers = $db->query("SELECT * FROM users WHERE user_id = :uid LIMIT 1", ['uid' => $targetUserId]);
        if (empty($existingUsers)) {
            respond([
                'status' => 'error',
                'message' => 'User account record not found.'
            ], 404);
        }
        $currentUserRec = $existingUsers[0];

        // 2. Resolve field values (use provided value if set & non-empty, otherwise retain existing DB record value)
        $firstName  = (isset($data['first_name']) && trim($data['first_name']) !== '') ? trim($data['first_name']) : $currentUserRec['first_name'];
        $middleName = isset($data['middle_name']) ? trim($data['middle_name']) : $currentUserRec['middle_name'];
        $lastName   = (isset($data['last_name']) && trim($data['last_name']) !== '') ? trim($data['last_name']) : $currentUserRec['last_name'];
        $email      = (isset($data['email']) && trim($data['email']) !== '') ? strtolower(trim($data['email'])) : $currentUserRec['email'];
        $mobileNumber = isset($data['mobile_number']) ? trim($data['mobile_number']) : $currentUserRec['mobile_number'];

        if (empty($firstName) || empty($lastName) || empty($email)) {
            respond([
                'status' => 'error',
                'message' => 'First Name, Last Name, and Email are required.'
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond([
                'status' => 'error',
                'message' => 'Please enter a valid email address.'
            ], 400);
        }

        // Email Uniqueness Check (Excluding self if email was modified)
        if (strtolower($email) !== strtolower($currentUserRec['email'])) {
            $dupEmail = $db->query(
                "SELECT user_id FROM users WHERE LOWER(email) = LOWER(:email) AND user_id != :uid LIMIT 1",
                ['email' => $email, 'uid' => $targetUserId]
            );
            if (!empty($dupEmail)) {
                respond([
                    'status' => 'error',
                    'message' => 'This email address is already in use by another user.'
                ], 400);
            }
        }

        // Mobile Uniqueness Check (Excluding self if mobile was modified)
        if (!empty($mobileNumber) && $mobileNumber !== $currentUserRec['mobile_number']) {
            $dupMobile = $db->query(
                "SELECT user_id FROM users WHERE mobile_number = :mobile AND user_id != :uid LIMIT 1",
                ['mobile' => $mobileNumber, 'uid' => $targetUserId]
            );
            if (!empty($dupMobile)) {
                respond([
                    'status' => 'error',
                    'message' => 'This mobile number is already in use by another user.'
                ], 400);
            }
        }

        // 3. Handle profile picture upload / reset
        $profilePicture = $currentUserRec['profile_picture'];

        if (isset($data['profile_picture'])) {
            $picData = trim($data['profile_picture']);

            if (empty($picData) || $picData === 'default-avatar.png') {
                $profilePicture = 'default-avatar.png';
            } else if (preg_match('/^data:image\/(\w+);base64,/', $picData, $type)) {
                $dataPart = substr($picData, strpos($picData, ',') + 1);
                $typeExt = strtolower($type[1]);
                if (in_array($typeExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    if ($typeExt === 'jpeg') $typeExt = 'jpg';
                    $decodedData = base64_decode($dataPart);
                    if ($decodedData !== false) {
                        $uploadDir = __DIR__ . '/../../uploads/avatars/';
                        if (!file_exists($uploadDir)) {
                            @mkdir($uploadDir, 0777, true);
                        }
                        $filename = 'avatar_' . $targetUserId . '_' . time() . '.' . $typeExt;
                        $filePath = $uploadDir . $filename;
                        if (file_put_contents($filePath, $decodedData)) {
                            $profilePicture = 'uploads/avatars/' . $filename;
                        }
                    }
                }
            } else {
                $profilePicture = $picData;
            }
        }

        $updatePayload = [
            'first_name' => $firstName,
            'middle_name' => $middleName ?: null,
            'last_name' => $lastName,
            'email' => $email,
            'mobile_number' => $mobileNumber ?: null,
            'profile_picture' => $profilePicture,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $db->update('users', $updatePayload, ['user_id' => $targetUserId]);

        // Update position if position_name supplied and user has position_id
        if (!empty($data['position_name']) && !empty($currentUserRec['position_id'])) {
            $db->update('positions', [
                'position_name' => trim($data['position_name']),
                'updated_at' => date('Y-m-d H:i:s')
            ], ['position_id' => $currentUserRec['position_id']]);
        }

        // Update Session State
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['email'] = $email;
        $_SESSION['profile_picture'] = $profilePicture;

        // Audit Trail
        try {
            $db->insert('audit_logs', [
                'actor_user_id' => $targetUserId,
                'action' => 'Update Profile',
                'target_table' => 'users',
                'target_id' => (string)$targetUserId,
                'description' => "User updated their personal profile details.",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'request_method' => $method,
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '/api/employee/profile.php',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'status' => 'Success'
            ]);
        } catch (Throwable $auditEx) {}

        respond([
            'status' => 'success',
            'message' => 'Your profile details have been updated successfully!'
        ]);
    }

    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);

} catch (Throwable $e) {
    error_log("Profile API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal database server error occurred. Please try again later.'
    ], 500);
}
?>
