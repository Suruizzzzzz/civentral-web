<?php
/**
 * Legacy endpoint wrapper for /api/citizen/logout.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/auth/logout', $_SERVER['REQUEST_METHOD']);
