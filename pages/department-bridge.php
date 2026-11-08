<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verify user is authenticated
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// 2. Validate department query parameter
$dept = $_GET['dept'] ?? '';
if ($dept !== 'education') {
    http_response_code(400);
    echo 'Invalid or unconfigured department.';
    exit;
}

// 3. Load dependencies & configuration
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/JwtService.php';

// 4. Resolve destination URL
$targetUrl = rtrim(getenv('EDUCATION_PORTAL_URL') ?: ($_ENV['EDUCATION_PORTAL_URL'] ?? 'https://education.civentral.tech'), '/');

// 5. Issue short-lived JWT token (2-minute single-use validity)
putenv('JWT_EXPIRY=120');
$jwtService = new \App\Services\JwtService();
$token = $jwtService->issueToken([
    'user_id' => (int) $_SESSION['user_id'],
    'employee_id' => $_SESSION['employee_id'] ?? null,
    'role_id' => isset($_SESSION['role_id']) ? (int) $_SESSION['role_id'] : null,
    'aud' => 'education',
]);

// 6. 302 Redirect to Education SSO callback
header('Location: ' . $targetUrl . '/sso/callback.php?sso_token=' . urlencode($token), true, 302);
exit;
