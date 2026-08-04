<?php
/**
 * Legacy endpoint wrapper for /api/citizen/update-status.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/accounts/status', $_SERVER['REQUEST_METHOD']);
