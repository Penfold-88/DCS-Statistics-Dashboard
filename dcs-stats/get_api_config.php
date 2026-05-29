<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

// Include config helper
require_once __DIR__ . '/api_config_helper.php';

// Load configuration with auto-fix
$configResult = loadApiConfigWithFix();
$config = $configResult['config'];

// Return only the public browser settings. The private DCSServerBot host,
// base URL, and API key stay server-side behind api_proxy.php.
echo json_encode([
    'use_api' => !empty($config['use_api']) && !empty($config['api_base_url']),
    'proxy_available' => !empty($config['api_base_url']),
    'timeout' => $config['timeout'] ?? 30,
    'cache_ttl' => $config['cache_ttl'] ?? 300,
    'refresh_interval' => $config['refresh_interval'] ?? 300
]);
