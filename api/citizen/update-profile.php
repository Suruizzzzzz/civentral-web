<?php
/**
 * Legacy endpoint wrapper for /api/citizen/update-profile.php
 * Delegates request handling to Citizen REST API Gateway.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/profile/update', $_SERVER['REQUEST_METHOD']);
