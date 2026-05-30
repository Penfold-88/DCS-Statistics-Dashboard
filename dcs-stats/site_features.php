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
        @mkdir($primaryDir, 0700, true);
        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }
    }
    
    // Try alternative data directory
    $altDir = __DIR__ . '/data';
    if (!is_dir($altDir)) {
        @mkdir($altDir, 0700, true);
    }
    if (is_dir($altDir) && is_writable($altDir)) {
        return $altDir . '/site_settings.json';
    }
    
    // Fall back to temp directory
    $tempDir = sys_get_temp_dir() . '/dcs_stats';
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0700, true);
    }
    
    return $tempDir . '/site_settings.json';
}

// Load settings from JSON file
function loadSiteFeatures() {
    if (array_key_exists('dcs_site_features_cache', $GLOBALS)) {
        return $GLOBALS['dcs_site_features_cache'];
    }

    // Load site configuration if exists
    $siteConfigFile = __DIR__ . '/site_config.json';
    $siteConfig = [];
    if (file_exists($siteConfigFile)) {
        $content = @file_get_contents($siteConfigFile);
        if ($content) {
            $siteConfig = json_decode($content, true) ?: [];
        }
    }
    
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
        'home_api_insights' => true,
        'home_attendance_cards' => true,
        'home_api_players_24h' => true,
        'home_api_players_7d' => true,
        'home_api_players_30d' => true,
        'home_api_current_players' => true,
        'home_top_theatres' => true,
        'home_top_missions' => true,
        'home_top_modules' => true,
        
        // Leaderboard Features
        'leaderboard_kills' => true,
        'leaderboard_deaths' => true,
        'leaderboard_kd_ratio' => true,
        'leaderboard_pvp_kd_ratio' => true,
        'leaderboard_credits' => true,
        'leaderboard_playtime' => true,
        'leaderboard_sorties' => false,
        'leaderboard_takeoffs' => true,
        'leaderboard_landings' => true,
        'leaderboard_crashes' => true,
        'leaderboard_ejections' => true,
        'leaderboard_aircraft' => true,
        'leaderboard_chart' => true,
        
        // Pilot Statistics Features
        'pilot_search' => true,
        'pilot_detailed_stats' => true,
        'pilot_mission_history' => true,
        'pilot_combat_stats' => true,
        'pilot_flight_stats' => true,
        'pilot_session_stats' => true,
        'pilot_aircraft_chart' => true,
        'pilot_carrier_traps' => true,
        
        // Credits System
        'credits_enabled' => true,
        'credits_leaderboard' => true,
        
        // Squadron Features
        'squadrons_enabled' => false,
        'squadron_management' => false,
        'squadron_statistics' => false,
        
        // Server Features
        'server_live_api_details' => true,
        'server_detail_status' => true,
        'server_detail_description' => true,
        'server_detail_mission' => true,
        'server_detail_slots' => true,
        'server_detail_restart' => true,
        'server_detail_weather' => true,
        'server_detail_extensions' => true,
        'server_detail_active_players' => true,
        'server_scope_filter' => true,
        
        // Global Features
        'show_discord_link' => true,
        'show_last_update' => true,
        'nav_custom_links' => true,
        
        // Custom Links
        'show_squadron_homepage' => false,
        'discord_link_url' => $siteConfig['discord_invite_url'] ?? 'https://discord.gg/DNENf6pUNX',
        'squadron_homepage_url' => '',
        'squadron_homepage_text' => 'Squadron',
        'custom_links_menu_text' => 'Squadron Links',
        'custom_links' => []
    ];
    
    // Get settings file path
    $settingsFile = getSettingsPath();
    
    // Load saved settings
    if (file_exists($settingsFile)) {
        $content = @file_get_contents($settingsFile);
        if ($content) {
            $saved = json_decode($content, true);
            if ($saved) {
                $GLOBALS['dcs_site_features_cache'] = array_merge($defaults, $saved);
                return $GLOBALS['dcs_site_features_cache'];
            }
        }
    }
    
    $GLOBALS['dcs_site_features_cache'] = $defaults;
    return $GLOBALS['dcs_site_features_cache'];
}

// Save settings
function saveSiteFeatures($features) {
    $settingsFile = getSettingsPath();
    
    // Ensure directory exists
    $dir = dirname($settingsFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    
    // Try to save
    $result = @file_put_contents($settingsFile, json_encode($features, JSON_PRETTY_PRINT));

    if ($result !== false) {
        @chmod($settingsFile, 0600);
        $GLOBALS['dcs_site_features_cache'] = $features;
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

function serverCardFeatureKey($serverName) {
    $slug = strtolower(trim((string)$serverName));
    $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
    $slug = trim($slug, '_');

    if ($slug === '') {
        $slug = 'unknown_server';
    }

    return 'server_card_' . $slug;
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
            'nav_servers' => 'Servers',
            'nav_custom_links' => 'Squadron Links Dropdown'
        ],
        'Homepage Sections' => [
            'home_server_stats' => 'Server Statistics Box',
            'home_player_activity' => 'Player Activity Graph',
            'home_mission_stats' => 'Mission Statistics Graph',
            'home_top_pilots' => 'Top Pilots Table',
            'home_recent_activity' => 'Recent Activity Feed',
            'home_api_insights' => 'Top 5 Insights',
            'home_attendance_cards' => 'Attendance Cards'
        ],
        'Attendance Cards' => [
            'home_api_players_24h' => 'Players 24h Card',
            'home_api_players_7d' => 'Players 7d Card',
            'home_api_players_30d' => 'Players 30d Card',
            'home_api_current_players' => 'Current Players Card'
        ],
        'Top 5 Insights' => [
            'home_top_theatres' => 'Top Theatres List',
            'home_top_missions' => 'Top Missions List',
            'home_top_modules' => 'Top Modules List'
        ],
        'Leaderboard Columns' => [
            'leaderboard_kills' => 'Kills Column',
            'leaderboard_deaths' => 'Deaths Column',
            'leaderboard_kd_ratio' => 'K/D Ratio Column',
            'leaderboard_pvp_kd_ratio' => 'PvP K/D Ratio Column',
            'leaderboard_credits' => 'Credits Column',
            'leaderboard_playtime' => 'Playtime Column',
            'leaderboard_sorties' => 'Sorties Column (Not Implimented)',
            'leaderboard_takeoffs' => 'Takeoffs Column',
            'leaderboard_landings' => 'Landings Column',
            'leaderboard_crashes' => 'Crashes Column',
            'leaderboard_ejections' => 'Ejections Column',
            'leaderboard_aircraft' => 'Most Used Aircraft Column',
            'leaderboard_chart' => 'Top 10 Chart'
        ],
        'Pilot Features' => [
            'pilot_search' => 'Pilot Search',
            'pilot_detailed_stats' => 'Detailed Statistics',
            'pilot_mission_history' => 'Mission History',
            'pilot_combat_stats' => 'Combat Statistics (Kills/Deaths)',
            'pilot_flight_stats' => 'Flight Statistics (Takeoffs/Landings)',
            'pilot_session_stats' => 'Last Session Statistics',
            'pilot_aircraft_chart' => 'Aircraft Usage Chart',
            'pilot_carrier_traps' => 'Carrier Landing / LSO Stats'
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
            'server_live_api_details' => 'Server Details Section',
            'server_detail_status' => 'Status Badge',
            'server_detail_description' => 'Server Description',
            'server_detail_mission' => 'Mission and Theatre Details',
            'server_detail_slots' => 'Slot Usage Details',
            'server_detail_restart' => 'Restart Time Details',
            'server_detail_weather' => 'Weather Details',
            'server_detail_extensions' => 'Extensions/SRS Details',
            'server_detail_active_players' => 'Active Players Details',
            'server_scope_filter' => 'Frontend Server Filter Dropdown'
        ],
        'Global Settings' => [
            'show_discord_link' => 'Show Discord Link',
            'show_last_update' => 'Show Last Update Time'
        ]
    ];
}

// Get feature dependencies (features that should be disabled when parent is disabled)
function getFeatureDependencies() {
    return [
        'credits_enabled' => ['credits_leaderboard', 'nav_pilot_credits'],
        'squadrons_enabled' => ['squadron_management', 'squadron_statistics', 'nav_squadrons'],
        'pilot_search' => ['pilot_detailed_stats', 'pilot_mission_history'],
        'nav_servers' => ['server_live_api_details'],
        'server_live_api_details' => ['server_detail_status', 'server_detail_description', 'server_detail_mission', 'server_detail_slots', 'server_detail_restart', 'server_detail_weather', 'server_detail_extensions', 'server_detail_active_players'],
        'home_api_insights' => ['home_top_theatres', 'home_top_missions', 'home_top_modules'],
        'home_attendance_cards' => ['home_api_players_24h', 'home_api_players_7d', 'home_api_players_30d', 'home_api_current_players']
    ];
}
