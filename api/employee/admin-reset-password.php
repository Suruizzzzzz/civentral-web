<?php
// Admin Reset Password — Administrator-initiated password reset for another staff member
// This endpoint is SEPARATE from the self-service change-password.php
// Requires active session with EDIT permission (Super Admin or Admin with EDIT action)

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

header('Access-Control-Allow-Methods: POST, OPTIONS');
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

if ($method !== 'POST') {
    respond([
        'status' => 'error',
        'message' => 'Method Not Allowed.'
    ], 405);
}

try {
    // 2. Authentication Check — Actor must be a logged-in administrator
    $actorUserId = $_SESSION['user_id'] ?? null;
    if (!$actorUserId) {
        respond([
            'status' => 'error',
            'message' => 'Unauthorized session. Please sign in.'
        ], 401);
    }

    // 3. Authorization Check — Actor must have EDIT permission or be Super Admin
    $actorInfo = $db->query("
        SELECT u.user_id, u.role_id, r.role_name, r.role_prefix, r.is_superadmin, r.is_global_access
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.role_id
        WHERE u.user_id = :uid
    ", ['uid' => $actorUserId]);

    if (empty($actorInfo)) {
        respond([
            'status' => 'error',
            'message' => 'Actor account not found.'
        ], 404);
    }

    $actorRow   = $actorInfo[0];
    $rolePrefix = strtoupper($actorRow['role_prefix'] ?? '');
    $roleName   = strtolower($actorRow['role_name'] ?? '');
    $isSuperAdmin = (!empty($actorRow['is_superadmin']) && intval($actorRow['is_superadmin']) === 1)
                    || in_array($rolePrefix, ['SA', 'SADM'])
                    || $roleName === 'super administrator'
                    || $roleName === 'superadmin';

    // Check for EDIT permission in role_permissions specifically for Users Account resource
    $hasEditPermission = false;
    if (!$isSuperAdmin && !empty($actorRow['role_id'])) {
        $permRows = $db->query("
            SELECT UPPER(a.action_name) AS action_name
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.permission_id
            JOIN actions a ON p.action_id = a.action_id
            JOIN resources res ON p.resource_id = res.resource_id
            WHERE rp.role_id = :rid
              AND UPPER(a.action_name) = 'EDIT'
              AND LOWER(TRIM(res.resource_name)) = 'users account'
        ", ['rid' => $actorRow['role_id']]) ?: [];

        if (!empty($permRows)) {
            $hasEditPermission = true;
        }
    }

    $canResetPassword = $isSuperAdmin || $hasEditPermission;

    if (!$canResetPassword) {
        respond([
            'status' => 'error',
            'message' => 'Forbidden. You do not have permission to reset staff account passwords.'
        ], 403);
    }

    // 4. Parse Request Body
    $rawInput     = file_get_contents('php://input');
    $data         = json_decode($rawInput, true) ?? $_POST;
    $action       = trim($data['action'] ?? '');
    $targetUserId = filter_var($data['target_user_id'] ?? null, FILTER_VALIDATE_INT);

    if (!$targetUserId) {
        respond([
            'status' => 'error',
            'message' => 'Valid target_user_id is required.'
        ], 400);
    }

    // 5. Fetch Target User
    $targetUsers = $db->select('users', ['user_id' => $targetUserId]);
    if (empty($targetUsers)) {
        respond([
            'status' => 'error',
            'message' => 'Target user account not found.'
        ], 404);
    }
    $targetUser = $targetUsers[0];

    // 6. Protect the main Super Administrator account
    if (strtoupper($targetUser['employee_id'] ?? '') === 'SA-2026-001') {
        respond([
            'status' => 'error',
            'message' => 'The main Super Administrator account password cannot be reset via this interface.'
        ], 403);
    }

    // 7. Prevent self-reset via admin interface
    if ((int)$targetUserId === (int)$actorUserId) {
        respond([
            'status' => 'error',
            'message' => 'You cannot reset your own password via the User Directory. Please use your Profile settings.'
        ], 403);
    }

    // ====================================================
    // ACTION A: send_otp — Generate and email OTP
    // ====================================================
    if ($action === 'send_otp') {

        $targetEmail    = $targetUser['email'] ?? '';
        $targetFullName = trim(($targetUser['first_name'] ?? '') . ' ' . ($targetUser['last_name'] ?? ''));

        if (empty($targetEmail)) {
            respond([
                'status' => 'error',
                'message' => 'Target user has no registered email address. Cannot dispatch authorization code.'
            ], 400);
        }

        // Invalidate any existing unused admin-reset OTPs for this target user
        try {
            $db->query(
                "UPDATE user_otps SET is_used = 1 WHERE user_id = :uid AND purpose = 'AdminPasswordReset' AND is_used = 0",
                ['uid' => $targetUserId]
            );
        } catch (Throwable $ex) {}

        // Generate secure 6-digit OTP
        $otpCode   = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Insert OTP record
        $db->insert('user_otps', [
            'user_id'    => $targetUserId,
            'otp_code'   => $otpCode,
            'purpose'    => 'AdminPasswordReset',
            'expires_at' => $expiresAt,
            'is_used'    => 0,
            'attempts'   => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Compose and send email
        $emailSubject = "CIVENTRAL — Password Reset Authorization Code";
        $emailBody    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                <div style='text-align: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;'>
                    <h2 style='color: #0f172a; margin: 0; font-size: 20px; font-weight: 900;'>CIVENTRAL PORTAL</h2>
                    <p style='color: #86B6F6; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 4px;'>Caloocan Municipal Management System</p>
                </div>
                <p style='color: #334155; font-size: 14px;'>Hello <strong>" . htmlspecialchars($targetFullName) . "</strong>,</p>
                <p style='color: #475569; font-size: 14px; line-height: 1.5;'>A system administrator has initiated a <strong>password reset</strong> for your account. Use the authorization code below to complete the process:</p>
                <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center;'>
                    <span style='font-size: 34px; font-weight: 900; letter-spacing: 8px; color: #165B7E; font-family: monospace;'>{$otpCode}</span>
                </div>
                <p style='color: #64748b; font-size: 12px; line-height: 1.5;'>This code is valid for <strong>10 minutes</strong> and can only be used once. If you did not expect this, please contact your system administrator immediately.</p>
                <div style='text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #f1f5f9;'>
                    <p style='color: #94a3b8; font-size: 11px;'>This is an automated system notification. Please do not reply directly to this email.</p>
                </div>
            </div>
        ";

        try {
            sendSystemEmail($targetEmail, $targetFullName, $emailSubject, $emailBody);
        } catch (Throwable $mailEx) {
            error_log("Admin Reset Password — Email dispatch error: " . $mailEx->getMessage());
        }

        // Audit log
        \App\Services\AuditLogger::log([
            'action'        => 'Admin Reset Password OTP Sent',
            'target_table'  => 'users',
            'target_id'     => (string)$targetUserId,
            'description'   => "Administrator dispatched a password reset OTP to staff account ({$targetUser['employee_id']}). OTP sent to: {$targetEmail}",
            'actor_user_id' => $actorUserId,
            'status'        => 'Initiated'
        ]);

        respond([
            'status'  => 'success',
            'message' => "Authorization code dispatched to {$targetEmail}. The code is valid for 10 minutes."
        ]);
    }

    // ====================================================
    // ACTION B: verify_and_reset — Verify OTP and reset password
    // ====================================================
    if ($action === 'verify_and_reset') {
        $otpCode = trim($data['otp_code'] ?? '');

        if (empty($otpCode) || strlen($otpCode) !== 6 || !ctype_digit($otpCode)) {
            respond([
                'status' => 'error',
                'message' => 'Please enter a valid 6-digit authorization code.'
            ], 400);
        }

        // Fetch the most recent unused AdminPasswordReset OTP for target user
        $otps = $db->query(
            "SELECT * FROM user_otps WHERE user_id = :uid AND purpose = 'AdminPasswordReset' AND is_used = 0 ORDER BY otp_id DESC LIMIT 1",
            ['uid' => $targetUserId]
        );

        if (empty($otps)) {
            respond([
                'status' => 'error',
                'message' => 'No active authorization code found. Please request a new code.'
            ], 400);
        }

        $otpRecord       = $otps[0];
        $otpId           = intval($otpRecord['otp_id']);
        $currentAttempts = intval($otpRecord['attempts'] ?? 0) + 1;

        // Check expiry
        if (strtotime($otpRecord['expires_at']) < time()) {
            try { $db->update('user_otps', ['is_used' => 1], ['otp_id' => $otpId]); } catch (Throwable $ex) {}
            respond([
                'status' => 'error',
                'message' => 'Authorization code has expired. Please request a new code.'
            ], 400);
        }

        // Check max attempts (5)
        if ($currentAttempts > 5) {
            try { $db->update('user_otps', ['is_used' => 1], ['otp_id' => $otpId]); } catch (Throwable $ex) {}
            respond([
                'status' => 'error',
                'message' => 'Too many failed attempts. Authorization code invalidated. Please request a new code.'
            ], 403);
        }

        // Increment attempts counter
        try { $db->update('user_otps', ['attempts' => $currentAttempts], ['otp_id' => $otpId]); } catch (Throwable $ex) {}

        // Verify OTP code
        if ($otpRecord['otp_code'] !== $otpCode) {
            $remaining = 5 - $currentAttempts;
            $failMsg   = ($remaining > 0)
                ? "Invalid authorization code. {$remaining} attempt(s) remaining."
                : "Maximum attempts exceeded. Please request a new code.";

            respond([
                'status' => 'error',
                'message' => $failMsg
            ], 400);
        }

        // Mark OTP as verified and used
        try {
            $db->update('user_otps', [
                'is_used'     => 1,
                'verified_at' => date('Y-m-d H:i:s')
            ], ['otp_id' => $otpId]);
        } catch (Throwable $ex) {}

        // Generate secure temporary password
        $chars    = 'abcdefghijklmnopqrstuvwxyz';
        $uppers   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nums     = '0123456789';
        $specials = '!@#$&*';
        $tempPass = $uppers[random_int(0, 25)]
                  . $chars[random_int(0, 25)]
                  . $chars[random_int(0, 25)]
                  . $nums[random_int(0, 9)]
                  . $nums[random_int(0, 9)]
                  . $specials[random_int(0, 5)]
                  . $chars[random_int(0, 25)]
                  . $chars[random_int(0, 25)];

        $hashedPassword = password_hash($tempPass, PASSWORD_BCRYPT);
        $targetFullName = trim(($targetUser['first_name'] ?? '') . ' ' . ($targetUser['last_name'] ?? ''));
        $targetEmail    = $targetUser['email'] ?? '';

        // Update target user password and force first-login password change
        $db->update('users', [
            'password'            => $hashedPassword,
            'is_first_login'      => 1,
            'password_changed_at' => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s')
        ], ['user_id' => $targetUserId]);

        // Email new temporary password to target user
        $emailSubject = "CIVENTRAL — Your Password Has Been Reset";
        $emailBody    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                <div style='text-align: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;'>
                    <h2 style='color: #0f172a; margin: 0; font-size: 20px; font-weight: 900;'>CIVENTRAL PORTAL</h2>
                    <p style='color: #86B6F6; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 4px;'>Caloocan Municipal Management System</p>
                </div>
                <p style='color: #334155; font-size: 14px;'>Hello <strong>" . htmlspecialchars($targetFullName) . "</strong>,</p>
                <p style='color: #475569; font-size: 14px; line-height: 1.5;'>A system administrator has successfully reset your account password. Your new temporary credentials are:</p>
                <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>
                        <tr>
                            <td style='padding: 6px 0; color: #64748b; font-weight: bold;'>Registered Email:</td>
                            <td style='padding: 6px 0; color: #0f172a; font-weight: bold; text-align: right;'>{$targetEmail}</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #64748b; font-weight: bold;'>Temporary Password:</td>
                            <td style='padding: 6px 0; color: #176B87; font-family: monospace; font-weight: 900; font-size: 15px; text-align: right;'>{$tempPass}</td>
                        </tr>
                    </table>
                </div>
                <p style='color: #475569; font-size: 12px; line-height: 1.5;'>You will be required to set a new password upon your next sign-in for security compliance.</p>
                <div style='text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #f1f5f9;'>
                    <p style='color: #94a3b8; font-size: 11px;'>This is an automated system notification. Please do not reply directly to this email.</p>
                </div>
            </div>
        ";

        try {
            sendSystemEmail($targetEmail, $targetFullName, $emailSubject, $emailBody);
        } catch (Throwable $mailEx) {
            error_log("Admin Reset Password — Credential email error: " . $mailEx->getMessage());
        }

        // Audit log
        \App\Services\AuditLogger::logMutation([
            'action'        => 'Admin Reset Password',
            'target_table'  => 'users',
            'target_id'     => (string)$targetUserId,
            'description'   => "Administrator successfully reset the password for staff account ({$targetUser['employee_id']}). User will be required to change password on next login.",
            'actor_user_id' => $actorUserId,
            'old_data'      => ['employee_id' => $targetUser['employee_id'], 'password' => '[REDACTED]'],
            'new_data'      => ['employee_id' => $targetUser['employee_id'], 'password' => '[RESET]', 'is_first_login' => 1]
        ]);

        respond([
            'status'  => 'success',
            'message' => "Password for {$targetFullName} has been successfully reset. New temporary credentials have been dispatched to their registered email."
        ]);
    }

    // Unknown action
    respond([
        'status' => 'error',
        'message' => 'Invalid action. Expected: send_otp or verify_and_reset.'
    ], 400);

} catch (Throwable $e) {
    error_log("Admin Reset Password API Error: " . $e->getMessage());
    respond([
        'status' => 'error',
        'message' => 'An internal server error occurred. Please try again later.'
    ], 500);
}
?>
