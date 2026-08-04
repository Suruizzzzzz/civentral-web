<?php
/**
 * Legacy endpoint wrapper for /api/citizen/get-notifications.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/notifications', $_SERVER['REQUEST_METHOD']);
