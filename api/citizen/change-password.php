<?php
/**
 * Legacy endpoint wrapper for /api/citizen/change-password.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/profile/password', $_SERVER['REQUEST_METHOD']);
