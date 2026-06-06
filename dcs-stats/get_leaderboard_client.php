<?php
header('Content-Type: application/json');

// Load configuration
require_once __DIR__ . '/api_config_helper.php';
$config = loadApiConfigWithFix()['config'];

// Return configuration for client-side API calls
echo json_encode([
    'api_base_url' => $config['api_base_url'] ?? 'http://localhost:8080',
    'use_api' => $config['use_api'] ?? false,
    'timeout' => $config['timeout'] ?? 30
]);
