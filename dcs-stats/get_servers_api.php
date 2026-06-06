<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

// Include security functions
require_once __DIR__ . '/security_functions.php';
require_once __DIR__ . '/api_client_enhanced.php';

// Rate limiting
if (!checkRateLimit(60, 60)) {
    exit;
}

try {
    $client = createEnhancedAPIClient();
    $servers = $client->request('/servers', null, 'GET');

    echo json_encode([
        'data' => is_array($servers) ? $servers : [],
        'servers' => is_array($servers) ? $servers : [],
        'source' => 'api',
        'generated' => date('c')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Service temporarily unavailable',
        'servers' => [],
        'data' => [],
        'source' => 'api'
    ]);
}
