<?php
/**
 * Legacy endpoint wrapper for /api/citizen/get-accounts.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/accounts', $_SERVER['REQUEST_METHOD']);
