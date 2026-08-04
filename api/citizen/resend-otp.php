<?php
/**
 * Legacy endpoint wrapper for /api/citizen/resend-otp.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/auth/resend-otp', $_SERVER['REQUEST_METHOD']);
