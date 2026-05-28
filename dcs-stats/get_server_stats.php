<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

// Include required files
require_once __DIR__ . '/security_functions.php';
require_once __DIR__ . '/api_client_enhanced.php';
require_once __DIR__ . '/api_config_helper.php';

// Rate limiting
if (!checkRateLimit(60, 60)) {
    exit;
}

// Load API configuration
$config = loadApiConfigWithFix()['config'];

// Check if API is enabled
if (!$config || !$config['use_api']) {
    echo json_encode([
        'error' => 'API not configured',
        'totalPlayers' => 0,
        'totalKills' => 0,
        'totalDeaths' => 0,
        'top5Pilots' => [],
        'top3Squadrons' => []
    ]);
    exit;
}

try {
    $apiClient = createEnhancedAPIClient();

    $stats = $apiClient->getServerStats();
    $attendance = [];
    try {
        $attendance = $apiClient->getServerAttendance();
    } catch (Exception $e) {
        $attendance = [];
    }

    $leaderboard = $apiClient->getLeaderboard('kills', 5);
    $topPlayers = $leaderboard['items'] ?? $apiClient->getTopKills();
    $top5Pilots = array_map(function($pilot) {
        return [
            'name' => $pilot['nick'] ?? 'Unknown',
            'nick' => $pilot['nick'] ?? 'Unknown',
            'kills' => $pilot['kills'] ?? 0,
            'deaths' => $pilot['deaths'] ?? 0,
            'kdr' => $pilot['kdr'] ?? 0,
            'credits' => $pilot['credits'] ?? 0,
            'playtime' => $pilot['playtime'] ?? 0
        ];
    }, is_array($topPlayers) ? array_slice($topPlayers, 0, 5) : []);

    $squadrons = [];
    try {
        $squadrons = $apiClient->getSquadrons();
    } catch (Exception $e) {
        $squadrons = [];
    }

    $top3Squadrons = array_map(function($squadron) {
        return [
            'name' => $squadron['name'] ?? 'Unknown',
            'members' => isset($squadron['members']) && is_array($squadron['members']) ? count($squadron['members']) : 0,
            'credits' => $squadron['credits'] ?? 0
        ];
    }, array_slice(is_array($squadrons) ? $squadrons : [], 0, 3));

    echo json_encode([
        'totalPlayers' => $stats['totalPlayers'] ?? ($attendance['unique_players_30d'] ?? 0),
        'totalPlaytime' => $stats['totalPlaytime'] ?? 0,
        'avgPlaytime' => $stats['avgPlaytime'] ?? 0,
        'activePlayers' => $stats['activePlayers'] ?? ($attendance['current_players'] ?? 0),
        'totalSorties' => $stats['totalSorties'] ?? ($attendance['total_sorties'] ?? 0),
        'totalKills' => $stats['totalKills'] ?? ($attendance['total_kills'] ?? 0),
        'totalDeaths' => $stats['totalDeaths'] ?? ($attendance['total_deaths'] ?? 0),
        'totalPvPKills' => $stats['totalPvPKills'] ?? ($attendance['total_pvp_kills'] ?? 0),
        'totalPvPDeaths' => $stats['totalPvPDeaths'] ?? ($attendance['total_pvp_deaths'] ?? 0),
        'activityLastWeek' => $stats['daily_players'] ?? ($attendance['daily_trend'] ?? []),
        'attendance' => $attendance,
        'top5Pilots' => $top5Pilots,
        'top3Squadrons' => $top3Squadrons,
        'source' => 'api'
    ]);
    
} catch (Exception $e) {
    
    // Return error response
    echo json_encode([
        'error' => 'Service temporarily unavailable',
        'totalPlayers' => 0,
        'totalKills' => 0,
        'totalDeaths' => 0,
        'top5Pilots' => [],
        'top3Squadrons' => []
    ]);
}
