<?php
/**
 * Get Credits API Implementation
 * Fetches credits data from DCSServerBot API
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
    
    $leaderboard = $client->request('/leaderboard?what=credits&limit=100', null, 'GET');
    $players = $leaderboard['items'] ?? [];

    if ($players && is_array($players)) {
        $response['data'] = array_map(function($player) {
            return [
                'name' => $player['nick'] ?? 'Unknown',
                'nick' => $player['nick'] ?? 'Unknown',
                'credits' => $player['credits'] ?? 0,
                'kills' => $player['kills'] ?? 0,
                'deaths' => $player['deaths'] ?? 0,
                'kdr' => $player['kdr'] ?? 0
            ];
        }, $players);
        $response['total_count'] = $leaderboard['total_count'] ?? count($players);
        $response['source'] = 'api';
    } else {
        $response['error'] = 'No credits data available';
    }
    
} catch (Exception $e) {
    $response['error'] = 'Failed to fetch credits: ' . $e->getMessage();
}

echo json_encode($response);
