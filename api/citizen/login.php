<?php
/**
 * Legacy endpoint wrapper for /api/citizen/login.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/auth/login', $_SERVER['REQUEST_METHOD']);
