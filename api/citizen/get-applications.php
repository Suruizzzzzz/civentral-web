<?php
/**
 * Legacy endpoint wrapper for /api/citizen/get-applications.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/applications', $_SERVER['REQUEST_METHOD']);
