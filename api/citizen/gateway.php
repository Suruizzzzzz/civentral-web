<?php
/**
 * CIVENTRAL - Citizen REST API Gateway Router
 * Central Gateway handling authentication, directory, accounts, profile, and security endpoints for citizens.
 */

ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Standardized Dynamic CORS Policy
$allowedOrigins = [
    'http://localhost',
    'http://localhost:80',
    'http://localhost:3000',
    'http://localhost:8081',
    'http://127.0.0.1',
    'http://127.0.0.1:80',
    'http://127.0.0.1:8081'
];

if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (in_array($origin, $allowedOrigins) || preg_match('/^http:\/\/(localhost|127\.0\.0\.1|192\.168\.\d+\.\d+|10\.\d+\.\d+\.\d+)(:\d+)?$/', $origin)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
    }
} else {
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Internal-Service-Key');

// Handle HTTP Preflight OPTIONS Request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/mailer.php';
require_once __DIR__ . '/../../config/sms.php';

/**
 * Standardized API Response Helper
 */
function respond(array $payload, int $statusCode = 200): void {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Get Request Body / Input
 */
function getRequestInput(): array {
    $rawInput = file_get_contents('php://input');
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        return array_merge($_GET, $decoded);
    }
    return array_merge($_GET, $_POST);
}

/**
 * Main Gateway Dispatcher
 */
function handleCitizenGateway(string $path = '', string $method = ''): void {
    global $db;

    if (empty($method)) {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    if (empty($path)) {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);
        
        $cleanPath = parse_url($requestUri, PHP_URL_PATH);
        if (strpos($cleanPath, $baseDir) === 0) {
            $path = substr($cleanPath, strlen($baseDir));
        } else {
            $path = $cleanPath;
        }
    }

    $path = '/' . trim($path, '/');
    $input = getRequestInput();

    // REST Route Dispatcher
    switch (true) {
        // Health Check & Gateway Overview
        case ($path === '/' || $path === '/health' || $path === '/index.php'):
            respond([
                'status' => 'success',
                'service' => 'CIVENTRAL Citizen REST API Gateway',
                'version' => '1.0.0',
                'endpoints' => [
                    'POST /api/citizen/auth/login' => 'Citizen login',
                    'POST /api/citizen/auth/register' => 'Citizen registration',
                    'POST /api/citizen/auth/verify-otp' => 'Verify OTP',
                    'POST /api/citizen/auth/resend-otp' => 'Resend OTP',
                    'POST /api/citizen/auth/check-account' => 'Check account existence',
                    'GET  /api/citizen/profile' => 'Get citizen profile',
                    'POST|PUT|PATCH /api/citizen/profile/password' => 'Change password',
                    'GET  /api/citizen/directory' => 'Get citizen directory',
                    'GET  /api/citizen/accounts' => 'Get citizen accounts audit',
                    'POST|PUT|PATCH /api/citizen/accounts/status' => 'Update citizen account status'
                ]
            ]);
            break;

        // AUTH: LOGIN
        case ($path === '/auth/login' || $path === '/login' || $path === '/login.php'):
            if ($method !== 'POST') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleLogin($input, $db);
            break;

        // AUTH: REGISTER
        case ($path === '/auth/register' || $path === '/register' || $path === '/register.php'):
            if ($method !== 'POST') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleRegister($input, $db);
            break;

        // AUTH: VERIFY OTP
        case ($path === '/auth/verify-otp' || $path === '/verify-otp' || $path === '/verify-otp.php' || $path === '/verify' || $path === '/verify.php'):
            if ($method !== 'POST') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleVerifyOTP($input, $db);
            break;

        // AUTH: RESEND OTP
        case ($path === '/auth/resend-otp' || $path === '/resend-otp' || $path === '/resend-otp.php'):
            if ($method !== 'POST') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleResendOTP($input, $db);
            break;

        // AUTH: CHECK ACCOUNT
        case ($path === '/auth/check-account' || $path === '/check-account' || $path === '/check-account.php'):
            if ($method !== 'POST') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleCheckAccount($input, $db);
            break;

        // AUTH: LOGOUT
        case ($path === '/auth/logout' || $path === '/logout' || $path === '/logout.php'):
            if ($method !== 'POST') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleLogout($input, $db);
            break;

        // AUTH: FORGOT PASSWORD
        case ($path === '/auth/forgot-password' || $path === '/forgot-password' || $path === '/forgot-password.php'):
            if ($method !== 'POST') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleForgotPassword($input, $db);
            break;

        // AUTH: RESET PASSWORD
        case ($path === '/auth/reset-password' || $path === '/reset-password' || $path === '/reset-password.php'):
            if ($method !== 'POST') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleResetPassword($input, $db);
            break;

        // INTERNAL SERVICE: CITIZEN IDENTITY LOOKUP
        case ($path === '/internal/profile' || $path === '/internal/get-profile'):
            if ($method !== 'GET') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleInternalCitizenProfile($input, $db);
            break;

        // PROFILE: GET PROFILE
        case ($path === '/profile' || $path === '/get-profile' || $path === '/get-profile.php'):
            if (!in_array($method, ['GET', 'POST'])) respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleGetProfile($input, $db);
            break;

        // PROFILE: UPDATE PROFILE
        case ($path === '/profile/update' || $path === '/update-profile' || $path === '/update-profile.php'):
            if (!in_array($method, ['POST', 'PUT', 'PATCH'])) respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleUpdateProfile($input, $db);
            break;

        // PROFILE: CHANGE PASSWORD
        case ($path === '/profile/password' || $path === '/change-password' || $path === '/change-password.php'):
            if (!in_array($method, ['POST', 'PUT', 'PATCH'])) respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleChangePassword($input, $db);
            break;

        // CITIZEN APPLICATIONS
        case ($path === '/applications' || $path === '/get-applications' || $path === '/get-applications.php'):
            if (!in_array($method, ['GET', 'POST'])) respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleGetApplications($input, $db);
            break;

        // CITIZEN NOTIFICATIONS
        case ($path === '/notifications' || $path === '/get-notifications' || $path === '/get-notifications.php'):
            if (!in_array($method, ['GET', 'POST'])) respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleGetNotifications($input, $db);
            break;

        // ADMIN/STAFF: DIRECTORY
        case ($path === '/directory' || $path === '/get-directory' || $path === '/get-directory.php'):
            if ($method !== 'GET') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleGetDirectory($db);
            break;

        // ADMIN/STAFF: ACCOUNTS AUDIT
        case ($path === '/accounts' || $path === '/get-accounts' || $path === '/get-accounts.php'):
            if ($method !== 'GET') respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleGetAccounts($db);
            break;

        // ADMIN/STAFF: UPDATE STATUS
        case ($path === '/accounts/status' || $path === '/update-status' || $path === '/update-status.php'):
            if (!in_array($method, ['POST', 'PUT', 'PATCH'])) respond(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
            handleUpdateStatus($input, $db);
            break;

        default:
            respond([
                'status' => 'error',
                'message' => "Resource endpoint not found: [{$method}] {$path}"
            ], 404);
            break;
    }
}

// -----------------------------------------------------------------------------
// HELPERS FOR EXTENDED CITIZEN TABLES
// -----------------------------------------------------------------------------

function logCitizenLoginAttempt($db, ?int $citizenUserId, ?int $sessionId, string $status, ?string $failureReason = null): void {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db->insert('citizen_login_history', [
            'citizen_user_id' => $citizenUserId,
            'session_id' => $sessionId,
            'login_time' => date('Y-m-d H:i:s'),
            'ip_address' => $ip,
            'login_status' => $status,
            'failure_reason' => $failureReason
        ]);
    } catch (Throwable $e) {
        error_log("Failed to log citizen login history: " . $e->getMessage());
    }
}

function createCitizenSession($db, int $citizenUserId, string $platform = 'Web', ?string $deviceId = null, ?string $pushToken = null): array {
    try {
        $rawRefreshToken = bin2hex(random_bytes(32));
        $refreshTokenHash = hash('sha256', $rawRefreshToken);
        $loginIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        $sessionId = $db->insert('citizen_sessions', [
            'citizen_user_id' => $citizenUserId,
            'device_id' => $deviceId ?: ('device_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8)),
            'refresh_token_hash' => $refreshTokenHash,
            'push_token' => $pushToken,
            'platform' => in_array($platform, ['Android', 'iOS', 'Web']) ? $platform : 'Web',
            'login_ip' => $loginIp,
            'is_revoked' => 0,
            'last_active_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expiresAt
        ]);

        return [
            'session_id' => $sessionId,
            'refresh_token' => $rawRefreshToken,
            'expires_at' => $expiresAt
        ];
    } catch (Throwable $e) {
        error_log("Failed to create citizen session: " . $e->getMessage());
        return ['session_id' => null, 'refresh_token' => null, 'expires_at' => null];
    }
}

// -----------------------------------------------------------------------------
// ENDPOINT HANDLERS
// -----------------------------------------------------------------------------

function handleLogin(array $input, $db): void {
    $emailOrMobile = strtolower(trim($input['email'] ?? $input['username'] ?? $input['mobile_number'] ?? ''));
    $password = trim($input['password'] ?? '');
    $platform = trim($input['platform'] ?? 'Web');
    $deviceId = trim($input['device_id'] ?? '');

    if (empty($emailOrMobile) || empty($password)) {
        logCitizenLoginAttempt($db, null, null, 'Failed', 'Missing credentials');
        respond(['status' => 'error', 'message' => 'Please provide both Email / Mobile Number and Password.'], 400);
    }

    try {
        $sql = "SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email_val) OR mobile_number = :mobile_val LIMIT 1";
        $users = $db->query($sql, [
            'email_val' => $emailOrMobile,
            'mobile_val' => $emailOrMobile
        ]);

        if (empty($users)) {
            logCitizenLoginAttempt($db, null, null, 'Failed', 'Account not found');
            respond(['status' => 'error', 'message' => 'Invalid email/mobile number or password.'], 401);
        }

        $user = $users[0];
        $citizenUserId = intval($user['citizen_user_id']);

        if ($user['status'] === 'Locked') {
            logCitizenLoginAttempt($db, $citizenUserId, null, 'Failed', 'Account locked');
            respond(['status' => 'error', 'message' => 'Account is locked due to excessive failed attempts. Please reset your password or contact support.'], 403);
        }

        if ($user['status'] === 'Inactive' || $user['status'] === 'Archived') {
            logCitizenLoginAttempt($db, $citizenUserId, null, 'Failed', 'Account inactive');
            respond(['status' => 'error', 'message' => 'Account is inactive. Please contact support.'], 403);
        }

        if (password_verify($password, $user['password'])) {
            try {
                $db->update('citizen_users', ['failed_attempts' => 0], ['citizen_user_id' => $citizenUserId]);
            } catch (Throwable $e) {}

            if ($user['status'] === 'Pending') {
                $_SESSION['pending_citizen_user_id'] = $citizenUserId;
                logCitizenLoginAttempt($db, $citizenUserId, null, 'Failed', 'Email verification required');
                respond([
                    'status' => 'verification_required',
                    'message' => 'Email verification required.',
                    'email' => $user['email']
                ]);
            }

            try {
                $db->update('citizen_users', ['last_login' => date('Y-m-d H:i:s')], ['citizen_user_id' => $citizenUserId]);
            } catch (Throwable $e) {}

            $_SESSION['citizen_user_id'] = $citizenUserId;

            // Create active citizen session record
            $sessionInfo = createCitizenSession($db, $citizenUserId, $platform, $deviceId);
            $sessionId = $sessionInfo['session_id'];

            // Log successful login attempt
            logCitizenLoginAttempt($db, $citizenUserId, $sessionId, 'Success');

            $middlePart = !empty($user['middle_name']) ? trim($user['middle_name']) . ' ' : '';
            $suffixPart = !empty($user['suffix']) ? ' ' . trim($user['suffix']) : '';
            $fullName = trim(($user['first_name'] ?? '') . ' ' . $middlePart . ($user['last_name'] ?? '') . $suffixPart);

            respond([
                'status' => 'success',
                'message' => 'Login successful.',
                'user' => [
                    'citizen_user_id' => $citizenUserId,
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'full_name' => $fullName,
                    'email' => $user['email'],
                    'mobile_number' => $user['mobile_number']
                ],
                'session' => [
                    'session_id' => $sessionId,
                    'refresh_token' => $sessionInfo['refresh_token'],
                    'expires_at' => $sessionInfo['expires_at']
                ]
            ]);
        } else {
            $failedAttempts = intval($user['failed_attempts'] ?? 0) + 1;
            $updates = ['failed_attempts' => $failedAttempts];

            if ($failedAttempts >= 5) {
                $updates['status'] = 'Locked';
                $db->update('citizen_users', $updates, ['citizen_user_id' => $citizenUserId]);
                logCitizenLoginAttempt($db, $citizenUserId, null, 'Failed', '5 consecutive failed password attempts - Account Locked');
                respond(['status' => 'error', 'message' => 'Account locked due to 5 consecutive failed login attempts.'], 403);
            } else {
                $db->update('citizen_users', $updates, ['citizen_user_id' => $citizenUserId]);
                logCitizenLoginAttempt($db, $citizenUserId, null, 'Failed', 'Incorrect password');
                $remaining = 5 - $failedAttempts;
                respond(['status' => 'error', 'message' => "Invalid password. {$remaining} attempt(s) remaining before account lockout."], 401);
            }
        }
    } catch (Throwable $e) {
        error_log("Citizen Login Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'An internal database server error occurred.'], 500);
    }
}

function handleRegister(array $input, $db): void {
    $firstName = trim($input['first_name'] ?? '');
    $middleName = trim($input['middle_name'] ?? '');
    $hasNoMiddleName = !empty($input['has_no_middle_name']) ? 1 : 0;
    $lastName = trim($input['last_name'] ?? '');
    $suffix = trim($input['suffix'] ?? '');
    $email = strtolower(trim($input['email'] ?? ''));
    $mobileNumber = trim($input['mobile_number'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        respond(['status' => 'error', 'message' => 'First Name, Last Name, Email, and Password are required.'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(['status' => 'error', 'message' => 'Please enter a valid email address.'], 400);
    }

    if (strlen($password) < 8) {
        respond(['status' => 'error', 'message' => 'Password must be at least 8 characters long.'], 400);
    }

    try {
        try { $db->query("SET innodb_lock_wait_timeout = 5;"); } catch (Throwable $e) {}

        if (!empty($email)) {
            $existingEmail = $db->query("SELECT citizen_user_id, status FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
            
            if (!empty($existingEmail)) {
                $existing = $existingEmail[0];
                if ($existing['status'] === 'Pending') {
                    $citizenUserId = intval($existing['citizen_user_id']);
                } else {
                    respond(['status' => 'error', 'message' => 'An account with this email address already exists. Please use a different email or sign in.'], 409);
                }
            }
        }

        if (!empty($mobileNumber)) {
            $sqlMobile = "SELECT citizen_user_id FROM citizen_users WHERE mobile_number = :mobile";
            $paramsMobile = ['mobile' => $mobileNumber];
            if ($citizenUserId) {
                $sqlMobile .= " AND citizen_user_id != :cid";
                $paramsMobile['cid'] = $citizenUserId;
            }
            $existingMobile = $db->query($sqlMobile, $paramsMobile);
            if (!empty($existingMobile)) {
                respond(['status' => 'error', 'message' => 'This mobile number is already associated with another account. Please use a different number or sign in.'], 409);
            }
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $userData = [
            'first_name' => $firstName,
            'middle_name' => $hasNoMiddleName ? null : $middleName,
            'has_no_middle_name' => $hasNoMiddleName,
            'last_name' => $lastName,
            'suffix' => $suffix,
            'email' => $email,
            'mobile_number' => $mobileNumber ?: null,
            'password' => $passwordHash,
            'status' => 'Pending'
        ];

        if ($citizenUserId) {
            $db->update('citizen_users', $userData, ['citizen_user_id' => $citizenUserId]);
        } else {
            $citizenUserId = $db->insert('citizen_users', $userData);
        }

        if (!$citizenUserId) {
            respond(['status' => 'error', 'message' => 'Failed to create user account.'], 500);
        }

        $_SESSION['pending_citizen_user_id'] = $citizenUserId;

        $otpCode = sprintf('%06d', mt_rand(0, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $db->insert('citizen_otps', [
            'citizen_user_id' => $citizenUserId,
            'otp_code' => $otpCode,
            'purpose' => 'Registration',
            'expires_at' => $expiresAt,
            'is_used' => 0
        ]);

        $middlePart = !empty($middleName) ? $middleName . ' ' : '';
        $recipientName = trim("{$firstName} {$middlePart}{$lastName}");
        sendOTPEmail($email, $recipientName, $otpCode, 'Registration');
        if (!empty($mobileNumber)) {
            sendIprogSMSOTP($mobileNumber);
        }

        $msg = !empty($mobileNumber)
            ? 'Account created. Verification code sent to your mobile number.'
            : 'Account created. Verification code sent to your email.';

        respond([
            'status' => 'success',
            'verification_required' => true,
            'message' => $msg,
            'email' => $email,
            'mobile_number' => $mobileNumber
        ], 201);
    } catch (Throwable $e) {
        error_log("Citizen Register Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'Registration error: ' . $e->getMessage()], 500);
    }
}

function handleVerifyOTP(array $input, $db): void {
    $otpCode = trim($input['otp'] ?? $input['otp_code'] ?? '');
    $mobileNumber = trim($input['mobile_number'] ?? $input['phone'] ?? $input['identifier'] ?? '');
    $email = strtolower(trim($input['email'] ?? ''));
    $purpose = trim($input['purpose'] ?? 'Registration');

    if (!in_array($purpose, ['Registration', 'Login', 'Password Reset'])) {
        $purpose = 'Registration';
    }

    if (empty($otpCode) || strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
        respond(['status' => 'error', 'message' => 'Please enter a valid 6-digit verification code.'], 400);
    }

    try {
        try { $db->query("SET innodb_lock_wait_timeout = 5;"); } catch (Throwable $e) {}

        $citizenUserId = $_SESSION['pending_citizen_user_id'] ?? null;
        $citizenUser = null;

        if (!empty($email)) {
            $users = $db->query("SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
            if (!empty($users)) {
                $citizenUser = $users[0];
                $citizenUserId = intval($citizenUser['citizen_user_id']);
            }
        }

        if (!$citizenUser && !empty($mobileNumber)) {
            $users = $db->query("SELECT * FROM citizen_users WHERE mobile_number = :mobile LIMIT 1", ['mobile' => $mobileNumber]);
            if (!empty($users)) {
                $citizenUser = $users[0];
                $citizenUserId = intval($citizenUser['citizen_user_id']);
            }
        }

        if (!$citizenUser && $citizenUserId) {
            $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $citizenUserId]);
            if (!empty($users)) {
                $citizenUser = $users[0];
            }
        }

        if (!$citizenUser || !$citizenUserId) {
            respond(['status' => 'error', 'message' => 'Citizen user account not found. Please register or check your account.'], 400);
        }

        // Dedicated IPROG SMS OTP Verification
        $userMobile = $citizenUser['mobile_number'] ?? $mobileNumber;
        $isSmsVerified = false;
        if (!empty($userMobile)) {
            $isSmsVerified = verifyIprogSMSOTP($userMobile, $otpCode);
        }

        // Database Email OTP Verification (Fallback / Parallel)
        $isEmailVerified = false;
        $otps = $db->query(
            "SELECT * FROM citizen_otps WHERE citizen_user_id = :cid AND purpose = :purpose AND is_used = 0 ORDER BY otp_id DESC LIMIT 1",
            ['cid' => $citizenUserId, 'purpose' => $purpose]
        );

        if (!empty($otps)) {
            $otpRecord = $otps[0];
            $currentTime = date('Y-m-d H:i:s');
            if ($currentTime <= $otpRecord['expires_at'] && $otpRecord['otp_code'] === $otpCode) {
                $isEmailVerified = true;
                $db->update('citizen_otps', ['is_used' => 1], ['otp_id' => $otpRecord['otp_id']]);
            }
        }

        if (!$isSmsVerified && !$isEmailVerified) {
            respond(['status' => 'error', 'message' => 'Incorrect or expired verification code. Please check and try again.'], 400);
        }

        if ($purpose === 'Registration' || strtolower($purpose) === 'registration' || $citizenUser['status'] === 'Pending') {
            $db->update('citizen_users', [
                'status' => 'Active',
                'failed_attempts' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['citizen_user_id' => $citizenUserId]);

            unset($_SESSION['pending_citizen_user_id']);
            $_SESSION['citizen_user_id'] = $citizenUserId;

            respond([
                'status' => 'success',
                'message' => 'Account successfully verified and activated! Welcome to CivCentral.',
                'user' => [
                    'citizen_user_id' => $citizenUserId,
                    'first_name' => $citizenUser['first_name'],
                    'last_name' => $citizenUser['last_name'],
                    'email' => $citizenUser['email']
                ]
            ]);
        } else {
            respond(['status' => 'success', 'message' => 'Verification code confirmed successfully.']);
        }
    } catch (Throwable $e) {
        error_log("Verify OTP Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'Server error during verification.'], 500);
    }
}

function handleResendOTP(array $input, $db): void {
    $email = strtolower(trim($input['email'] ?? ''));
    $purpose = trim($input['purpose'] ?? 'Registration');

    if (!in_array($purpose, ['Registration', 'Login', 'Password Reset'])) {
        $purpose = 'Registration';
    }

    try {
        $citizenUserId = $_SESSION['pending_citizen_user_id'] ?? $_SESSION['citizen_user_id'] ?? null;
        $citizenUser = null;

        if (!empty($email)) {
            $users = $db->query("SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
            if (!empty($users)) {
                $citizenUser = $users[0];
                $citizenUserId = intval($citizenUser['citizen_user_id']);
            }
        } elseif ($citizenUserId) {
            $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $citizenUserId]);
            if (!empty($users)) {
                $citizenUser = $users[0];
            }
        }

        if (!$citizenUser || !$citizenUserId) {
            respond(['status' => 'error', 'message' => 'Citizen user account not found.'], 404);
        }

        $latestOtps = $db->query(
            "SELECT created_at FROM citizen_otps WHERE citizen_user_id = :cid AND purpose = :purpose ORDER BY otp_id DESC LIMIT 1",
            ['cid' => $citizenUserId, 'purpose' => $purpose]
        );

        if (!empty($latestOtps)) {
            $lastSentTime = strtotime($latestOtps[0]['created_at']);
            $secondsPassed = time() - $lastSentTime;
            if ($secondsPassed < 60) {
                $wait = 60 - $secondsPassed;
                respond(['status' => 'error', 'message' => "Please wait {$wait} seconds before requesting another code."], 429);
            }
        }

        $otpCode = sprintf('%06d', mt_rand(0, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $db->insert('citizen_otps', [
            'citizen_user_id' => $citizenUserId,
            'otp_code' => $otpCode,
            'purpose' => $purpose,
            'expires_at' => $expiresAt,
            'is_used' => 0
        ]);

        $recipientEmail = $citizenUser['email'];
        $middlePart = !empty($citizenUser['middle_name']) ? $citizenUser['middle_name'] . ' ' : '';
        $recipientName = trim("{$citizenUser['first_name']} {$middlePart}{$citizenUser['last_name']}");

        sendOTPEmail($recipientEmail, $recipientName, $otpCode, $purpose);
        if (!empty($citizenUser['mobile_number'])) {
            sendIprogSMSOTP($citizenUser['mobile_number']);
        }

        respond(['status' => 'success', 'message' => 'A new verification code has been sent to your email address.']);
    } catch (Throwable $e) {
        error_log("Resend OTP Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'Server error during OTP resend.'], 500);
    }
}

function handleCheckAccount(array $input, $db): void {
    $identifier = strtolower(trim($input['email'] ?? $input['identifier'] ?? $input['mobile_number'] ?? ''));

    if (empty($identifier)) {
        respond(['status' => 'error', 'message' => 'Please enter an email address or phone number.'], 400);
    }

    try {
        $sql = "SELECT citizen_user_id, status FROM citizen_users WHERE LOWER(email) = LOWER(:email_val) OR mobile_number = :mobile_val LIMIT 1";
        $users = $db->query($sql, ['email_val' => $identifier, 'mobile_val' => $identifier]);

        if (!empty($users)) {
            respond([
                'status' => 'exists',
                'exists' => true,
                'user_status' => $users[0]['status'],
                'message' => 'Account exists.'
            ]);
        } else {
            respond([
                'status' => 'not_found',
                'exists' => false,
                'message' => 'Account does not exist.'
            ]);
        }
    } catch (Throwable $e) {
        error_log("Check Account Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'Database error during account check.'], 500);
    }
}

/**
 * Internal server-to-server citizen identity lookup.
 * Returns only the identity fields needed by internal CIVENTRAL services.
 */
function handleInternalCitizenProfile(array $input, $db): void {
    $expectedKey = trim((string)(getenv('CIVENTRAL_INTERNAL_SERVICE_KEY') ?: ($_ENV['CIVENTRAL_INTERNAL_SERVICE_KEY'] ?? '')));

    if ($expectedKey === '') {
        respond([
            'status' => 'error',
            'message' => 'Internal service authentication is not configured.'
        ], 503);
    }

    $providedKey = trim((string)($_SERVER['HTTP_X_INTERNAL_SERVICE_KEY'] ?? ''));
    if ($providedKey === '' || !hash_equals($expectedKey, $providedKey)) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized internal service request.'
        ], 401);
    }

    $citizenUserId = filter_var(
        $input['citizen_user_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!$citizenUserId) {
        respond([
            'status' => 'error',
            'message' => 'A valid citizen_user_id is required.'
        ], 422);
    }

    try {
        $users = $db->query(
            "SELECT citizen_user_id, first_name, middle_name, has_no_middle_name, last_name, suffix
             FROM citizen_users
             WHERE citizen_user_id = :id
             LIMIT 1",
            ['id' => (int)$citizenUserId]
        );

        if (empty($users)) {
            respond([
                'status' => 'error',
                'message' => 'Citizen user not found.'
            ], 404);
        }

        $user = $users[0];
        $middlePart = !empty($user['middle_name']) ? trim((string)$user['middle_name']) . ' ' : '';
        $suffixPart = !empty($user['suffix']) ? ' ' . trim((string)$user['suffix']) : '';
        $fullName = trim(
            ($user['first_name'] ?? '') . ' ' .
            $middlePart .
            ($user['last_name'] ?? '') .
            $suffixPart
        );

        respond([
            'status' => 'success',
            'data' => [
                'citizen_user_id' => (int)$user['citizen_user_id'],
                'first_name' => $user['first_name'] ?? '',
                'middle_name' => $user['middle_name'] ?? '',
                'has_no_middle_name' => !empty($user['has_no_middle_name']),
                'last_name' => $user['last_name'] ?? '',
                'suffix' => $user['suffix'] ?? '',
                'full_name' => $fullName,
            ]
        ]);
    } catch (Throwable $e) {
        error_log('Internal Citizen Profile Lookup Error: ' . $e->getMessage());
        respond([
            'status' => 'error',
            'message' => 'An internal database server error occurred.'
        ], 500);
    }
}
function handleGetProfile(array $input, $db): void {
    try {
        $explicitUserId = filter_var($input['citizen_user_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $email = strtolower(trim($input['email'] ?? ''));
        $mobileNumber = trim($input['mobile_number'] ?? $input['phone'] ?? $input['identifier'] ?? '');
        $sessionUserId = $_SESSION['citizen_user_id'] ?? null;

        $user = null;
        if (!empty($email)) {
            $users = $db->query("SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
            if (!empty($users)) $user = $users[0];
        }

        if (!$user && !empty($mobileNumber)) {
            $users = $db->query("SELECT * FROM citizen_users WHERE mobile_number = :mobile LIMIT 1", ['mobile' => $mobileNumber]);
            if (!empty($users)) $user = $users[0];
        }

        if (!$user && $explicitUserId) {
            $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $explicitUserId]);
            if (!empty($users)) $user = $users[0];
        }

        if (!$user && $sessionUserId) {
            $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $sessionUserId]);
            if (!empty($users)) $user = $users[0];
        }

        if (!$user) {
            respond(['status' => 'error', 'message' => 'Unauthorized session or citizen user not found. Please sign in.'], 401);
        }

        $activeUserId = intval($user['citizen_user_id']);
        $middlePart = !empty($user['middle_name']) ? trim($user['middle_name']) . ' ' : '';
        $suffixPart = !empty($user['suffix']) ? ' ' . trim($user['suffix']) : '';
        $fullName = trim(($user['first_name'] ?? '') . ' ' . $middlePart . ($user['last_name'] ?? '') . $suffixPart);
        $initials = strtoupper(substr($user['first_name'] ?? 'C', 0, 1) . substr($user['last_name'] ?? 'U', 0, 1));
        $createdDate = !empty($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'N/A';
        $lastLoginFormatted = !empty($user['last_login']) ? date('F j, Y, g:i A', strtotime($user['last_login'])) : 'N/A';

        respond([
            'status' => 'success',
            'data' => [
                'citizen_user_id' => $activeUserId,
                'first_name' => $user['first_name'] ?? '',
                'middle_name' => $user['middle_name'] ?? '',
                'has_no_middle_name' => !empty($user['has_no_middle_name']),
                'last_name' => $user['last_name'] ?? '',
                'suffix' => $user['suffix'] ?? '',
                'full_name' => $fullName,
                'initials' => $initials,
                'email' => $user['email'] ?? '',
                'mobile_number' => $user['mobile_number'] ?? '',
                'status' => $user['status'] ?? 'Pending',
                'registry_completed' => !empty($user['registry_completed']),
                'biometric_enabled' => !empty($user['biometric_enabled']),
                'last_login' => $lastLoginFormatted,
                'member_since' => $createdDate,
                'created_at' => $user['created_at']
            ]
        ]);
    } catch (Throwable $e) {
        error_log("Get Citizen Profile Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'An internal database server error occurred.'], 500);
    }
}

function handleChangePassword(array $input, $db): void {
    $citizenUserId = intval($input['citizen_user_id'] ?? $_SESSION['citizen_user_id'] ?? 0);
    $email = trim($input['email'] ?? '');
    $currentPassword = trim($input['current_password'] ?? '');
    $newPassword = trim($input['new_password'] ?? '');

    if (empty($currentPassword) || empty($newPassword)) {
        respond(['status' => 'error', 'message' => 'Please provide both your current password and your new password.'], 400);
    }

    if ($citizenUserId <= 0 && empty($email)) {
        respond(['status' => 'error', 'message' => 'Invalid user credentials.'], 400);
    }

    $hasUppercase = preg_match('/[A-Z]/', $newPassword);
    $hasLowercase = preg_match('/[a-z]/', $newPassword);
    $hasNumber = preg_match('/[0-9]/', $newPassword);
    $hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword);
    $isLongEnough = strlen($newPassword) >= 8;

    if (!$isLongEnough || !$hasUppercase || !$hasLowercase || !$hasNumber || !$hasSpecial) {
        respond(['status' => 'error', 'message' => 'New password must be strong (at least 8 characters, and contain uppercase, lowercase, number, and special character).'], 400);
    }

    try {
        if ($citizenUserId > 0) {
            $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $citizenUserId]);
        } else {
            $users = $db->query("SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
        }

        if (empty($users)) {
            respond(['status' => 'error', 'message' => 'User account not found.'], 404);
        }

        $user = $users[0];
        $targetUserId = intval($user['citizen_user_id']);

        if (!password_verify($currentPassword, $user['password'])) {
            respond(['status' => 'error', 'message' => 'Current password entered is incorrect.'], 400);
        }

        if (password_verify($newPassword, $user['password'])) {
            respond(['status' => 'error', 'message' => 'New password cannot be the same as your old password.'], 400);
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $db->update('citizen_users', [
            'password' => $newPasswordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['citizen_user_id' => $targetUserId]);

        respond(['status' => 'success', 'message' => 'Password updated successfully.']);
    } catch (Throwable $e) {
        error_log("Change Citizen Password Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'An internal database server error occurred.'], 500);
    }
}

function handleGetDirectory($db): void {
    try {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            respond(['status' => 'error', 'message' => 'Unauthorized session. Please sign in to view the citizen directory.'], 401);
        }

        $users = $db->query("SELECT * FROM citizen_users ORDER BY created_at DESC");

        $formattedUsers = [];
        foreach ($users as $user) {
            $middlePart = !empty($user['middle_name']) ? trim($user['middle_name']) . ' ' : '';
            $suffixPart = !empty($user['suffix']) ? ' ' . trim($user['suffix']) : '';
            $fullName = trim(($user['first_name'] ?? '') . ' ' . $middlePart . ($user['last_name'] ?? '') . $suffixPart);
            
            $createdYear = !empty($user['created_at']) ? date('Y', strtotime($user['created_at'])) : date('Y');
            $idStr = 'CIT-' . $createdYear . '-' . str_pad($user['citizen_user_id'], 4, '0', STR_PAD_LEFT);
            
            $lastLoginFormatted = !empty($user['last_login']) ? date('M d, Y g:i A', strtotime($user['last_login'])) : 'Never';
            $createdDate = !empty($user['created_at']) ? date('Y-m-d', strtotime($user['created_at'])) : '';
            $barangay = 'Unspecified';

            $formattedUsers[] = [
                'id' => $idStr,
                'db_id' => $user['citizen_user_id'],
                'name' => $fullName,
                'email' => $user['email'] ?? '',
                'barangay' => $barangay,
                'status' => $user['status'] ?? 'Pending',
                'regDate' => $createdDate,
                'lastLogin' => $lastLoginFormatted,
                'services' => [
                    'scholarship' => 'No App',
                    'permit' => 'No App',
                    'welfare' => 'No App'
                ]
            ];
        }

        respond(['status' => 'success', 'data' => $formattedUsers]);
    } catch (Throwable $e) {
        error_log("Get Citizen Directory Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'An internal database server error occurred.'], 500);
    }
}

function handleGetAccounts($db): void {
    try {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            respond(['status' => 'error', 'message' => 'Unauthorized session. Please sign in to view citizen accounts.'], 401);
        }

        $users = $db->query("SELECT * FROM citizen_users ORDER BY created_at DESC");

        $formattedUsers = [];
        foreach ($users as $user) {
            $middlePart = !empty($user['middle_name']) ? trim($user['middle_name']) . ' ' : '';
            $suffixPart = !empty($user['suffix']) ? ' ' . trim($user['suffix']) : '';
            $fullName = trim(($user['first_name'] ?? '') . ' ' . $middlePart . ($user['last_name'] ?? '') . $suffixPart);
            
            $createdYear = !empty($user['created_at']) ? date('Y', strtotime($user['created_at'])) : date('Y');
            $idStr = 'CIT-' . $createdYear . '-' . str_pad($user['citizen_user_id'], 4, '0', STR_PAD_LEFT);
            
            $status = $user['status'] ?? 'Active';
            $failedAttempts = intval($user['failed_attempts'] ?? 0);
            $flagged = ($status === 'Locked' || $status === 'Suspended' || $failedAttempts > 0);
            
            $violations = '0 failed login attempts';
            if ($status === 'Locked') {
                $violations = 'Locked by administrator';
            } else if ($status === 'Suspended') {
                $violations = 'Flagged: Security Investigation Hold';
            } else if ($status === 'Inactive') {
                $violations = 'Deactivated by administrator';
            } else if ($failedAttempts > 0) {
                $violations = "{$failedAttempts} failed login attempt(s)";
            }

            $timeline = [
                [
                    'action' => "Account Status: {$status}",
                    'admin' => "System",
                    'date' => !empty($user['updated_at']) ? date('F j, Y', strtotime($user['updated_at'])) : date('F j, Y'),
                    'reason' => "Current security status evaluated as {$status}."
                ],
                [
                    'action' => "Registration Completed",
                    'admin' => "System",
                    'date' => !empty($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : date('F j, Y'),
                    'reason' => "Citizen registered account."
                ]
            ];

            $formattedUsers[] = [
                'id' => $idStr,
                'db_id' => $user['citizen_user_id'],
                'name' => $fullName,
                'email' => $user['email'] ?? '',
                'status' => $status,
                'violations' => $violations,
                'flagged' => $flagged,
                'timeline' => $timeline
            ];
        }

        respond(['status' => 'success', 'data' => $formattedUsers]);
    } catch (Throwable $e) {
        error_log("Get Citizen Accounts Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'An internal database server error occurred.'], 500);
    }
}

function handleUpdateStatus(array $input, $db): void {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        respond(['status' => 'error', 'message' => 'Unauthorized session. Please sign in to update citizen account status.'], 401);
    }

    $citId = trim($input['citizen_id'] ?? $input['id'] ?? '');
    $dbId = filter_var($input['db_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $action = trim($input['action'] ?? '');
    $reason = trim($input['reason'] ?? '');

    if (empty($action)) {
        respond(['status' => 'error', 'message' => 'Action is required.'], 400);
    }

    try {
        $user = null;
        if ($dbId) {
            $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $dbId]);
            if (!empty($users)) $user = $users[0];
        } elseif (!empty($citId)) {
            $cleanId = preg_replace('/[^\d]/', '', $citId);
            if ($cleanId) {
                $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => intval($cleanId)]);
                if (!empty($users)) $user = $users[0];
            }
        }

        if (!$user) {
            respond(['status' => 'error', 'message' => 'Citizen user record not found.'], 404);
        }

        $targetUserId = intval($user['citizen_user_id']);
        $newStatus = $user['status'];
        $failedAttempts = intval($user['failed_attempts'] ?? 0);

        if (in_array($action, ['Activate', 'Unlock', 'Restore'])) {
            $newStatus = 'Active';
            $failedAttempts = 0;
        } elseif ($action === 'Deactivate') {
            $newStatus = 'Inactive';
        } elseif ($action === 'Lock') {
            $newStatus = 'Locked';
        } elseif ($action === 'Suspend') {
            $newStatus = 'Suspended';
        }

        $db->update('citizen_users', [
            'status' => $newStatus,
            'failed_attempts' => $failedAttempts,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['citizen_user_id' => $targetUserId]);

        respond([
            'status' => 'success',
            'message' => "Security status updated to {$newStatus}.",
            'new_status' => $newStatus
        ]);
    } catch (Throwable $e) {
        error_log("Update Citizen Status Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'An internal database server error occurred.'], 500);
    }
}

function handleUpdateProfile(array $input, $db): void {
    $citizenUserId = intval($input['citizen_user_id'] ?? $_SESSION['citizen_user_id'] ?? 0);
    $email = strtolower(trim($input['email'] ?? ''));
    $mobileNumber = trim($input['mobile_number'] ?? $input['phone'] ?? '');

    try {
        $user = null;
        if ($citizenUserId > 0) {
            $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :id LIMIT 1", ['id' => $citizenUserId]);
            if (!empty($users)) $user = $users[0];
        } elseif (!empty($email)) {
            $users = $db->query("SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
            if (!empty($users)) $user = $users[0];
        }

        if (!$user) {
            respond(['status' => 'error', 'message' => 'Citizen user account not found.'], 404);
        }

        $targetUserId = intval($user['citizen_user_id']);
        $updates = ['updated_at' => date('Y-m-d H:i:s')];
        
        if (!empty($mobileNumber)) {
            $updates['mobile_number'] = $mobileNumber;
        }

        $db->update('citizen_users', $updates, ['citizen_user_id' => $targetUserId]);

        respond([
            'status' => 'success',
            'message' => 'Profile updated successfully.'
        ]);
    } catch (Throwable $e) {
        error_log("Update Citizen Profile Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'Failed to update profile.'], 500);
    }
}

function handleGetApplications(array $input, $db): void {
    try {
        $email = strtolower(trim($input['email'] ?? ''));
        $citizenUserId = intval($input['citizen_user_id'] ?? $_SESSION['citizen_user_id'] ?? 0);

        // Fetch citizen user applications if any, default to structured response
        respond([
            'status' => 'success',
            'data' => []
        ]);
    } catch (Throwable $e) {
        error_log("Get Applications Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'Failed to fetch applications.'], 500);
    }
}

function handleGetNotifications(array $input, $db): void {
    try {
        $email = strtolower(trim($input['email'] ?? ''));

        // Default system alerts / notifications
        respond([
            'status' => 'success',
            'data' => [
                [
                    'notification_id' => 'ALT-1001',
                    'title' => 'Welcome to CIVentral',
                    'body' => 'Your municipal portal account is active. Access city services seamlessly.',
                    'category' => 'Broadcast',
                    'timestamp' => 'Just now',
                    'is_read' => false
                ]
            ]
        ]);
    } catch (Throwable $e) {
        error_log("Get Notifications Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'Failed to fetch notifications.'], 500);
    }
}

function handleLogout(array $input, $db): void {
    $citizenUserId = intval($_SESSION['citizen_user_id'] ?? $input['citizen_user_id'] ?? 0);
    $refreshToken = trim($input['refresh_token'] ?? '');

    if (!empty($refreshToken)) {
        $tokenHash = hash('sha256', $refreshToken);
        try {
            $db->update('citizen_sessions', ['is_revoked' => 1], ['refresh_token_hash' => $tokenHash]);
        } catch (Throwable $e) {}
    } elseif ($citizenUserId > 0) {
        try {
            $db->update('citizen_sessions', ['is_revoked' => 1], ['citizen_user_id' => $citizenUserId]);
        } catch (Throwable $e) {}
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        unset($_SESSION['citizen_user_id']);
        unset($_SESSION['pending_citizen_user_id']);
    }

    respond(['status' => 'success', 'message' => 'Successfully logged out.']);
}

function handleForgotPassword(array $input, $db): void {
    $email = strtolower(trim($input['email'] ?? ''));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(['status' => 'error', 'message' => 'Please provide a valid email address.'], 400);
    }

    try {
        $users = $db->query("SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);

        if (empty($users)) {
            respond(['status' => 'success', 'message' => 'If an account exists with this email, password reset instructions have been sent.']);
        }

        $user = $users[0];
        $citizenUserId = intval($user['citizen_user_id']);

        $resetCode = sprintf('%06d', mt_rand(0, 999999));
        $rawResetToken = bin2hex(random_bytes(32));
        $resetTokenHash = hash('sha256', $rawResetToken);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db->insert('citizen_password_resets', [
            'citizen_user_id' => $citizenUserId,
            'reset_token_hash' => $resetTokenHash,
            'expires_at' => $expiresAt,
            'used_at' => null
        ]);

        $middlePart = !empty($user['middle_name']) ? $user['middle_name'] . ' ' : '';
        $recipientName = trim("{$user['first_name']} {$middlePart}{$user['last_name']}");
        sendOTPEmail($email, $recipientName, $resetCode, 'Password Reset');

        respond([
            'status' => 'success',
            'message' => 'Password reset instructions and verification code sent to your email.',
            'email' => $email,
            'reset_token' => $rawResetToken
        ]);
    } catch (Throwable $e) {
        error_log("Forgot Password Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'Failed to process password reset request.'], 500);
    }
}

function handleResetPassword(array $input, $db): void {
    $rawToken = trim($input['reset_token'] ?? $input['token'] ?? '');
    $email = strtolower(trim($input['email'] ?? ''));
    $newPassword = trim($input['new_password'] ?? $input['password'] ?? '');

    if (empty($newPassword) || strlen($newPassword) < 8) {
        respond(['status' => 'error', 'message' => 'Password must be at least 8 characters long.'], 400);
    }

    try {
        $user = null;
        if (!empty($rawToken)) {
            $tokenHash = hash('sha256', $rawToken);
            $resets = $db->query("SELECT * FROM citizen_password_resets WHERE reset_token_hash = :hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1", ['hash' => $tokenHash]);
            if (!empty($resets)) {
                $resetRecord = $resets[0];
                $users = $db->query("SELECT * FROM citizen_users WHERE citizen_user_id = :cid LIMIT 1", ['cid' => $resetRecord['citizen_user_id']]);
                if (!empty($users)) {
                    $user = $users[0];
                    $db->update('citizen_password_resets', ['used_at' => date('Y-m-d H:i:s')], ['reset_id' => $resetRecord['reset_id']]);
                }
            }
        } elseif (!empty($email)) {
            $users = $db->query("SELECT * FROM citizen_users WHERE LOWER(email) = LOWER(:email) LIMIT 1", ['email' => $email]);
            if (!empty($users)) $user = $users[0];
        }

        if (!$user) {
            respond(['status' => 'error', 'message' => 'Invalid or expired password reset token.'], 400);
        }

        $citizenUserId = intval($user['citizen_user_id']);
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $db->update('citizen_users', [
            'password' => $newPasswordHash,
            'status' => 'Active',
            'failed_attempts' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['citizen_user_id' => $citizenUserId]);

        respond(['status' => 'success', 'message' => 'Password reset successfully. You may now log in with your new password.']);
    } catch (Throwable $e) {
        error_log("Reset Password Error: " . $e->getMessage());
        respond(['status' => 'error', 'message' => 'Failed to reset password.'], 500);
    }
}



