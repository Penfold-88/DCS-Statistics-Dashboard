<?php
/**
 * Get Mission Stats API Implementation
 * Fetches mission statistics from DCSServerBot API
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/api_client_enhanced.php';
require_once __DIR__ . '/security_functions.php';

// Rate limiting: 60 requests per minute
if (!checkRateLimit(60, 60)) {
    exit;
}

// Initialize response
$response = ['data' => [], 'error' => null];

try {
    // Create API client
    $client = createEnhancedAPIClient();
    
    // Get top kills leaderboard (includes kills, deaths, K/D)
    $topKills = $client->request('/topkills', null, 'GET');
    
    if ($topKills && is_array($topKills)) {
        $stats = [];
        
        foreach ($topKills as $player) {
            $stats[] = [
                "name" => htmlspecialchars($player['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                "kills" => intval($player['kills'] ?? 0),
                "deaths" => intval($player['deaths'] ?? 0),
                "sorties" => intval($player['sorties'] ?? 0),
                "missions" => intval($player['missions'] ?? 0),
                "points" => intval($player['points'] ?? 0)
            ];
        }
        
        // Sort by points
        usort($stats, function($a, $b) {
            return $b['points'] <=> $a['points'];
        });
        
        echo json_encode($stats);
    } else {
        echo json_encode([]);
    }
    
} catch (Exception $e) {
    echo json_encode([]);
}
