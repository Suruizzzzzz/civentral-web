<?php
/**
 * Legacy endpoint wrapper for /api/citizen/register.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/auth/register', $_SERVER['REQUEST_METHOD']);
