<?php
/**
 * Legacy endpoint wrapper for /api/citizen/forgot-password.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/auth/forgot-password', $_SERVER['REQUEST_METHOD']);
