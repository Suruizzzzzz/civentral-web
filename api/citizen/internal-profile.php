<?php
/**
 * Internal CIVENTRAL citizen identity lookup wrapper.
 * Delegates to the Citizen REST API Gateway internal profile route.
 */

require_once __DIR__ . '/gateway.php';

handleCitizenGateway('/internal/profile', $_SERVER['REQUEST_METHOD']);
