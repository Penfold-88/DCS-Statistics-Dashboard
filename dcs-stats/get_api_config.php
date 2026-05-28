<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

// Include config helper
require_once __DIR__ . '/api_config_helper.php';

// Load configuration with auto-fix
$configResult = loadApiConfigWithFix();
$config = $configResult['config'];

// Extract API host for client
$apiHost = $config['api_host'] ?? '';
if (!$apiHost && !empty($config['api_base_url'])) {
    $apiHost = preg_replace('#^https?://#', '', $config['api_base_url']);
}

// Store the config path if needed for debugging
$configPath = isset($configResult['config_path']) ? $configResult['config_path'] : 'default';

// Return configuration for client
echo json_encode([
    'api_host' => $apiHost,
    'api_base_url' => $config['api_base_url'] ?? '',
    'use_api' => true, // Always true
    'timeout' => $config['timeout'] ?? 30,
    'cache_ttl' => $config['cache_ttl'] ?? 300,
    'refresh_interval' => $config['refresh_interval'] ?? 300
]);
