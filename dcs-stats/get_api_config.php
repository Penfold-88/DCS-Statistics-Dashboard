<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

// Include config helper
require_once __DIR__ . '/api_config_helper.php';

// Load configuration with auto-fix
$configResult = loadApiConfigWithFix();
$config = $configResult['config'];

function safePublicConfigInt($value, int $default, int $min, int $max): int {
    $validated = filter_var($value, FILTER_VALIDATE_INT);
    if ($validated === false) {
        return $default;
    }

    return max($min, min($max, $validated));
}

$proxyAvailable = !empty($config['api_base_url']);
$timeout = safePublicConfigInt($config['timeout'] ?? 30, 30, 5, 300);
$refreshInterval = safePublicConfigInt($config['refresh_interval'] ?? 300, 300, 60, 3600);

// Return only the minimal browser-safe settings needed by the public frontend.
// The private DCSServerBot host, base URL, cache settings, and API key stay
// server-side behind api_proxy.php and the admin-only API settings pages.
echo json_encode([
    'use_api' => !empty($config['use_api']) && $proxyAvailable,
    'proxy_available' => $proxyAvailable,
    'timeout' => $timeout,
    'refresh_interval' => $refreshInterval
]);
