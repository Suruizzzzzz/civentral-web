<?php
/**
 * Legacy endpoint wrapper for /api/citizen/get-profile.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/profile', $_SERVER['REQUEST_METHOD']);
