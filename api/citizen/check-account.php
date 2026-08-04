<?php
/**
 * Legacy endpoint wrapper for /api/citizen/check-account.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/auth/check-account', $_SERVER['REQUEST_METHOD']);
