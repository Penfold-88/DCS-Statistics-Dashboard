<?php
/**
 * API Configuration Helper
 * Handles creation, validation, and auto-fixing of API configurations
 */

/**
 * Get the default/complete API configuration template
 */
function getDefaultApiConfig($apiHost = '') {
    return [
        // Connection settings
        'api_host' => $apiHost,
        'api_base_url' => $apiHost ? 'http://' . $apiHost : '', // Default to HTTP
        'api_key' => null,
        'timeout' => 30,
        'cache_ttl' => 300,
        'refresh_interval' => 300,
        'verify_ssl' => true,
        
        // Feature flags
        'use_api' => true, // Always use API
        
        // All available endpoints in our system
        'enabled_endpoints' => [
            'get_servers.php',
            'get_leaderboard.php',
            'get_player_stats.php',
            'get_pilot_credits.php',
            'get_pilot_statistics.php',
            'get_server_statistics.php',
            'get_active_players.php',
            'search_players.php',
            'get_leaderboard_client.php',
            'get_credits.php',
            'get_missionstats.php',
            'get_server_stats.php',
            'get_squadrons.php',
            'get_squadron_members.php',
            'get_squadron_credits.php'
        ],
        
        // DCSServerBot API endpoint mappings
        'endpoints' => [
            'getuser' => '/getuser',
            'stats' => '/stats',
            'player_info' => '/player_info',
            'topkills' => '/topkills',
            'topkdr' => '/topkdr',
            'leaderboard' => '/leaderboard',
            'highscore' => '/highscore',
            'trueskill' => '/trueskill',
            'modulestats' => '/modulestats',
            'traps' => '/traps',
            'weaponpk' => '/weaponpk',
            'credits' => '/credits',
            'servers' => '/servers',
            'serverstats' => '/serverstats',
            'server_attendance' => '/server_attendance',
            'current_server' => '/current_server',
            'squadrons' => '/squadrons',
            'player_squadrons' => '/player_squadrons',
            'squadron_members' => '/squadron_members',
            'squadron_credits' => '/squadron_credits'
        ]
    ];
}

function getEnvironmentApiKey() {
    $apiKey = getenv('DCSBOT_API_KEY');
    if ($apiKey === false || trim((string)$apiKey) === '') {
        return null;
    }

    return trim((string)$apiKey);
}

function isEnvironmentApiKeyActive() {
    return getEnvironmentApiKey() !== null;
}

function applyEnvironmentApiConfigOverrides($config) {
    if (!is_array($config)) {
        $config = getDefaultApiConfig();
    }

    $envApiKey = getEnvironmentApiKey();
    if ($envApiKey !== null) {
        $config['api_key'] = $envApiKey;
    }

    return $config;
}

/**
 * Validate and fix an existing API configuration
 */
function validateAndFixApiConfig($config) {
    $default = getDefaultApiConfig();
    $fixed = false;
    
    // Ensure it's an array
    if (!is_array($config)) {
        return ['config' => $default, 'fixed' => true, 'changes' => ['Replaced invalid config with default']];
    }
    
    $changes = [];
    
    // Extract api_host from api_base_url if missing
    if (empty($config['api_host']) && !empty($config['api_base_url'])) {
        $config['api_host'] = preg_replace('#^https?://#', '', $config['api_base_url']);
        $changes[] = 'Extracted api_host from api_base_url';
        $fixed = true;
    }
    
    // Ensure api_base_url has protocol
    if (!empty($config['api_host']) && empty($config['api_base_url'])) {
        $config['api_base_url'] = 'https://' . $config['api_host'];
        $changes[] = 'Generated api_base_url from api_host';
        $fixed = true;
    }
    
    // Fix missing required fields
    $requiredFields = ['timeout', 'cache_ttl', 'refresh_interval', 'use_api', 'verify_ssl'];
    foreach ($requiredFields as $field) {
        if (!isset($config[$field])) {
            $config[$field] = $default[$field];
            $changes[] = "Added missing field: $field";
            $fixed = true;
        }
    }
    
    // Remove deprecated fallback_to_json if present
    if (isset($config['fallback_to_json'])) {
        unset($config['fallback_to_json']);
        $changes[] = 'Removed deprecated fallback_to_json setting';
        $fixed = true;
    }
    
    // Ensure enabled_endpoints exists and has all endpoints
    if (!isset($config['enabled_endpoints']) || !is_array($config['enabled_endpoints'])) {
        $config['enabled_endpoints'] = $default['enabled_endpoints'];
        $changes[] = 'Added all enabled endpoints';
        $fixed = true;
    } else {
        // Add any missing endpoints
        $missingEndpoints = array_diff($default['enabled_endpoints'], $config['enabled_endpoints']);
        if (!empty($missingEndpoints)) {
            $config['enabled_endpoints'] = array_unique(array_merge($config['enabled_endpoints'], $missingEndpoints));
            $changes[] = 'Added missing endpoints: ' . implode(', ', $missingEndpoints);
            $fixed = true;
        }
    }
    
    // Ensure endpoints mapping exists and is complete
    if (!isset($config['endpoints']) || !is_array($config['endpoints'])) {
        $config['endpoints'] = $default['endpoints'];
        $changes[] = 'Added endpoint mappings';
        $fixed = true;
    } else {
        // Add any missing endpoint mappings
        foreach ($default['endpoints'] as $key => $value) {
            if (!isset($config['endpoints'][$key])) {
                $config['endpoints'][$key] = $value;
                $changes[] = "Added missing endpoint mapping: $key";
                $fixed = true;
            }
        }
    }
    
    // Validate data types
    if (!is_int($config['timeout']) || $config['timeout'] < 1) {
        $config['timeout'] = 30;
        $changes[] = 'Fixed invalid timeout value';
        $fixed = true;
    }
    
    if (!is_int($config['cache_ttl']) || $config['cache_ttl'] < 0) {
        $config['cache_ttl'] = 300;
        $changes[] = 'Fixed invalid cache_ttl value';
        $fixed = true;
    }

    if (!is_int($config['refresh_interval']) || $config['refresh_interval'] < 60) {
        $config['refresh_interval'] = 300;
        $changes[] = 'Fixed invalid refresh_interval value';
        $fixed = true;
    }

    $config['verify_ssl'] = filter_var($config['verify_ssl'], FILTER_VALIDATE_BOOLEAN);
    
    // Always ensure use_api is true
    if ($config['use_api'] !== true) {
        $config['use_api'] = true;
        $changes[] = 'Set use_api to true (API-only mode)';
        $fixed = true;
    }
    
    return [
        'config' => $config,
        'fixed' => $fixed,
        'changes' => $changes
    ];
}

/**
 * Get writable config file path
 */
function getWritableConfigPath($preferredFile = null) {
    if ($preferredFile === null) {
        $legacyFile = __DIR__ . '/api_config.json';
        if (file_exists($legacyFile)) {
            return $legacyFile;
        }
        $preferredFile = __DIR__ . '/site-config/data/api_config.json';
    }
    
    // First try the preferred location
    if (file_exists($preferredFile) && is_writable($preferredFile)) {
        return $preferredFile;
    }
    
    // Try to create in preferred location
    $dir = dirname($preferredFile);
    if (is_dir($dir) && is_writable($dir)) {
        return $preferredFile;
    }
    
    // Try site-config/data directory
    $dataDir = __DIR__ . '/site-config/data/';
    if (is_dir($dataDir) && is_writable($dataDir)) {
        return $dataDir . 'api_config.json';
    }
    
    // Try creating data directory
    $dataDir = __DIR__ . '/data/';
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0700, true);
        @chmod($dataDir, 0700);
    }
    if (is_writable($dataDir)) {
        return $dataDir . 'api_config.json';
    }
    
    // Try the current directory itself as last resort before temp
    if (is_writable(__DIR__)) {
        return __DIR__ . '/api_config.json';
    }
    
    // Fall back to system temp directory
    $tempDir = sys_get_temp_dir() . '/dcs_stats/';
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0700, true);
    }
    
    return $tempDir . 'api_config.json';
}

/**
 * Load API configuration with auto-fixing
 */
function loadApiConfigWithFix($configFile = null) {
    // Get a writable location for the config
    $configFile = getWritableConfigPath($configFile);
    
    // If file doesn't exist, create default config
    if (!file_exists($configFile)) {
        $defaultConfig = getDefaultApiConfig();
        @file_put_contents($configFile, json_encode($defaultConfig, JSON_PRETTY_PRINT));
        return [
            'config' => applyEnvironmentApiConfigOverrides($defaultConfig),
            'created' => true,
            'fixed' => false,
            'changes' => ['Created new configuration file'],
            'config_path' => $configFile
        ];
    }
    
    // Load existing config
    $configData = @file_get_contents($configFile);
    $config = json_decode($configData, true);
    
    // Validate and fix
    $result = validateAndFixApiConfig($config);
    
    // Save if changes were made
    if ($result['fixed']) {
        @file_put_contents($configFile, json_encode($result['config'], JSON_PRETTY_PRINT));
    }
    
    // Add config path to result
    $result['config_path'] = $configFile;
    $result['config'] = applyEnvironmentApiConfigOverrides($result['config']);
    
    return $result;
}

/**
 * Create or update API configuration from host
 */
function createApiConfigFromHost($apiHost, $configFile = null) {
    // Get a writable location for the config
    $configFile = getWritableConfigPath($configFile);
    
    // Clean the host
    $apiHost = preg_replace('#^https?://#', '', trim($apiHost));
    
    // Load existing config if any
    $existingConfig = [];
    if (file_exists($configFile)) {
        $existingConfig = json_decode(@file_get_contents($configFile), true) ?: [];
    }
    
    // Create complete config
    $newConfig = getDefaultApiConfig($apiHost);
    
    // Preserve any custom settings from existing config
    if (isset($existingConfig['api_key'])) {
        $newConfig['api_key'] = $existingConfig['api_key'];
    }
    
    // If we have an existing detected protocol, preserve it
    if (isset($existingConfig['api_base_url']) && strpos($existingConfig['api_base_url'], 'http://') === 0) {
        $newConfig['api_base_url'] = 'http://' . $apiHost;
    }
    
    // Save the config
    $saved = @file_put_contents($configFile, json_encode($newConfig, JSON_PRETTY_PRINT));
    
    // Even if saved to temp location, it's still a success
    return [
        'success' => true,
        'config' => $newConfig,
        'config_path' => $configFile,
        'message' => 'API configuration saved successfully' . 
                     ($configFile !== dirname(__DIR__) . '/api_config.json' ? ' (using alternative location)' : '')
    ];
}
?>
