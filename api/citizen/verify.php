<?php
/**
 * Legacy endpoint wrapper for /api/citizen/verify.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/auth/verify-otp', $_SERVER['REQUEST_METHOD']);
