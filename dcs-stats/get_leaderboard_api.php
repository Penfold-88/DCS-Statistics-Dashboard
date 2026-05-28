<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

// Include security functions and API client
require_once __DIR__ . '/security_functions.php';
require_once __DIR__ . '/api_client_enhanced.php';
require_once __DIR__ . '/api_config_helper.php';

// Rate limiting: 120 requests per minute
if (!checkRateLimit(120, 60)) {
    exit;
}

try {
    // Load API configuration
    $config = loadApiConfigWithFix()['config'];
    
    // Initialize API client
    $apiClient = new DCSServerBotAPIClient($config);
    
    $sortBy = $_GET['sort'] ?? 'kills';
    $limit = max(1, min(100, intval($_GET['limit'] ?? 10)));
    $leaderboard = $apiClient->getLeaderboard($sortBy, $limit);
    $topPlayers = $leaderboard['items'] ?? [];
    
    // Transform API response to match our existing format
    $stats = [];
    foreach ($topPlayers as $index => $player) {
        $playerInfo = null;
        $overall = [];
        $mostUsedAircraft = null;

        try {
            $playerInfo = $apiClient->getPlayerInfo($player['nick'] ?? '');
            $overall = $playerInfo['overall'] ?? [];
            $moduleKills = $overall['killsByModule'] ?? [];
            if (!empty($moduleKills) && is_array($moduleKills)) {
                $mostUsedAircraft = $moduleKills[0]['module'] ?? null;
            }
        } catch (Exception $detailError) {
            $overall = [];
        }

        $stats[] = [
            'rank' => $index + 1,
            'row_num' => $player['row_num'] ?? ($index + 1),
            'nick' => htmlspecialchars($player['nick'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
            'name' => htmlspecialchars($player['nick'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
            'kills' => $player['kills'] ?? ($overall['kills'] ?? 0),
            'deaths' => $player['deaths'] ?? ($overall['deaths'] ?? 0),
            'kd_ratio' => $player['kdr'] ?? ($overall['kdr'] ?? 0),
            'kdr' => $player['kdr'] ?? ($overall['kdr'] ?? 0),
            'kills_pvp' => $player['kills_pvp'] ?? ($overall['kills_pvp'] ?? 0),
            'deaths_pvp' => $player['deaths_pvp'] ?? ($overall['deaths_pvp'] ?? 0),
            'kdr_pvp' => $player['kdr_pvp'] ?? ($overall['kdr_pvp'] ?? 0),
            'playtime' => $player['playtime'] ?? ($overall['playtime'] ?? 0),
            'credits' => $player['credits'] ?? 0,
            'sorties' => $overall['sorties'] ?? null,
            'flight_hours' => $overall['flight_hours'] ?? null,
            'takeoffs' => $overall['takeoffs'] ?? null,
            'landings' => $overall['landings'] ?? null,
            'crashes' => $overall['crashes'] ?? null,
            'ejections' => $overall['ejections'] ?? null,
            'most_used_aircraft' => $mostUsedAircraft
        ];
    }
    
    // Return response
    echo json_encode([
        'data' => $stats,
        'source' => 'api',
        'count' => count($stats),
        'total_count' => $leaderboard['total_count'] ?? count($stats),
        'generated' => date('c')
    ]);
    
} catch (Exception $e) {
    
    // Return error response
    echo json_encode([
        'error' => 'Service temporarily unavailable',
        'data' => [],
        'source' => 'api',
        'count' => 0
    ]);
}
