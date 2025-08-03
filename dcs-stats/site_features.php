<?php
/**
 * Site Features Configuration
 * Controls which features/sections are enabled on the public site
 */

// Get writable path for settings
function getSettingsPath() {
    // Try primary location
    $primaryPath = __DIR__ . '/site-config/data/site_settings.json';
    $primaryDir = dirname($primaryPath);
    
    // Check if directory exists and is writable
    if (is_dir($primaryDir) && is_writable($primaryDir)) {
        return $primaryPath;
    }
    
    // Try to create directory
    if (!is_dir($primaryDir)) {
        @mkdir($primaryDir, 0777, true);
        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }
    }
    
    // Try alternative data directory
    $altDir = __DIR__ . '/data';
    if (!is_dir($altDir)) {
        @mkdir($altDir, 0777, true);
    }
    if (is_dir($altDir) && is_writable($altDir)) {
        return $altDir . '/site_settings.json';
    }
    
    // Fall back to temp directory
    $tempDir = sys_get_temp_dir() . '/dcs_stats';
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0777, true);
    }
    
    return $tempDir . '/site_settings.json';
}

// Load settings from JSON file
function loadSiteFeatures() {
    // Default features (all enabled)
    $defaults = [
        // Navigation Items
        'nav_home' => true,
        'nav_leaderboard' => true,
        'nav_pilot_credits' => true,
        'nav_pilot_statistics' => true,
        'nav_squadrons' => false,
        'nav_servers' => true,
        
        // Homepage Sections
        'home_server_stats' => true,
        'home_player_activity' => true,
        'home_mission_stats' => true,
        'home_top_pilots' => true,
        'home_recent_activity' => true,
        
        // Leaderboard Features
        'leaderboard_kills' => true,
        'leaderboard_deaths' => true,
        'leaderboard_kd_ratio' => true,
        'leaderboard_flight_hours' => true,
        'leaderboard_sorties' => true,
        'leaderboard_aircraft' => true,
        
        // Pilot Statistics Features
        'pilot_search' => true,
        'pilot_detailed_stats' => true,
        'pilot_mission_history' => true,
        'pilot_combat_stats' => true,
        'pilot_flight_stats' => true,
        'pilot_session_stats' => true,
        'pilot_aircraft_chart' => true,
        
        // Credits System
        'credits_enabled' => true,
        'credits_leaderboard' => true,
        
        // Squadron Features
        'squadrons_enabled' => false,
        'squadron_management' => false,
        'squadron_statistics' => false,
        
        // Server Features
        'servers_list' => true,
        'server_status' => true,
        'server_players' => true,
        
        // Global Features
        'show_discord_link' => true,
        'show_last_update' => true,
        'maintenance_mode' => false,
        'maintenance_whitelist' => '',
        
        // Custom Links
        'show_squadron_homepage' => false,
        'discord_link_url' => 'https://discord.gg/DNENf6pUNX',
        'squadron_homepage_url' => '',
        'squadron_homepage_text' => 'Squadron'
    ];
    
    // Get settings file path
    $settingsFile = getSettingsPath();
    
    // Load saved settings
    if (file_exists($settingsFile)) {
        $content = @file_get_contents($settingsFile);
        if ($content) {
            $saved = json_decode($content, true);
            if ($saved) {
                return array_merge($defaults, $saved);
            }
        }
    }
    
    return $defaults;
}

// Save settings
function saveSiteFeatures($features) {
    $settingsFile = getSettingsPath();
    
    // Ensure directory exists
    $dir = dirname($settingsFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    
    // Try to save
    $result = @file_put_contents($settingsFile, json_encode($features, JSON_PRETTY_PRINT));
    
    // If failed, try to make writable and retry
    if ($result === false) {
        @chmod($dir, 0777);
        @chmod($settingsFile, 0666);
        $result = @file_put_contents($settingsFile, json_encode($features, JSON_PRETTY_PRINT));
    }
    
    return $result !== false;
}

// Check if a feature is enabled
function isFeatureEnabled($feature) {
    static $features = null;
    if ($features === null) {
        $features = loadSiteFeatures();
    }
    return isset($features[$feature]) ? $features[$feature] : true;
}

// Get feature value (for URLs and text)
function getFeatureValue($feature, $default = '') {
    static $features = null;
    if ($features === null) {
        $features = loadSiteFeatures();
    }
    return isset($features[$feature]) ? $features[$feature] : $default;
}

// Feature groups for admin interface
function getFeatureGroups() {
    return [
        'Navigation' => [
            'nav_home' => 'Homepage',
            'nav_leaderboard' => 'Leaderboard',
            'nav_pilot_credits' => 'Pilot Credits',
            'nav_pilot_statistics' => 'Pilot Statistics',
            'nav_squadrons' => 'Squadrons',
            'nav_servers' => 'Servers'
        ],
        'Homepage Sections' => [
            'home_server_stats' => 'Server Statistics Box',
            'home_player_activity' => 'Player Activity Graph',
            'home_mission_stats' => 'Mission Statistics Graph',
            'home_top_pilots' => 'Top Pilots Table',
            'home_recent_activity' => 'Recent Activity Feed'
        ],
        'Leaderboard Columns' => [
            'leaderboard_kills' => 'Kills Column',
            'leaderboard_deaths' => 'Deaths Column',
            'leaderboard_kd_ratio' => 'K/D Ratio Column',
            'leaderboard_flight_hours' => 'Flight Hours Column',
            'leaderboard_sorties' => 'Sorties Column',
            'leaderboard_aircraft' => 'Most Used Aircraft Column'
        ],
        'Pilot Features' => [
            'pilot_search' => 'Pilot Search',
            'pilot_detailed_stats' => 'Detailed Statistics',
            'pilot_mission_history' => 'Mission History',
            'pilot_combat_stats' => 'Combat Statistics (Kills/Deaths)',
            'pilot_flight_stats' => 'Flight Statistics (Takeoffs/Landings)',
            'pilot_session_stats' => 'Last Session Statistics',
            'pilot_aircraft_chart' => 'Aircraft Usage Chart'
        ],
        'Credits System' => [
            'credits_enabled' => 'Enable Credits System',
            'credits_leaderboard' => 'Credits Leaderboard'
        ],
        'Squadron System' => [
            'squadrons_enabled' => 'Enable Squadrons',
            'squadron_management' => 'Squadron Management',
            'squadron_statistics' => 'Squadron Statistics'
        ],
        'Server Features' => [
            'servers_list' => 'Server List',
            'server_status' => 'Server Status Display',
            'server_players' => 'Online Players Display'
        ],
        'Global Settings' => [
            'show_discord_link' => 'Show Discord Link',
            'show_last_update' => 'Show Last Update Time',
            'maintenance_mode' => 'Enable Maintenance Mode'
        ]
    ];
}

// Get feature dependencies (features that should be disabled when parent is disabled)
function getFeatureDependencies() {
    return [
        'credits_enabled' => ['credits_leaderboard', 'nav_pilot_credits'],
        'squadrons_enabled' => ['squadron_management', 'squadron_statistics', 'nav_squadrons'],
        'pilot_search' => ['pilot_detailed_stats', 'pilot_mission_history'],
        'servers_list' => ['server_status', 'server_players', 'nav_servers']
    ];
}