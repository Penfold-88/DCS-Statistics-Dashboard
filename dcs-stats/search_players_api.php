<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

// Include security functions and API client
require_once __DIR__ . '/security_functions.php';
require_once __DIR__ . '/api_client_enhanced.php';
require_once __DIR__ . '/api_config_helper.php';

// Rate limiting
if (!checkRateLimit(60, 60)) {
    exit;
}

// Validate search query - frontend uses 'search' parameter
$query = validateInput($_GET['search'] ?? $_GET['q'] ?? '', [
    'type' => 'search_query',
    'max_length' => 50,
    'min_length' => 2
]);

if ($query === false) {
    echo json_encode(["error" => "Invalid search query"]);
    exit;
}

try {
    // Load API configuration
    $config = loadApiConfigWithFix()['config'];
    
    // Initialize API client
    $apiClient = new DCSServerBotAPIClient($config);
    
    // Note: The /getuser endpoint appears to be broken on many DCSServerBot instances
    // It often returns Internal Server Error (500)
    // As a workaround, we'll return a message explaining this limitation
    
    // Try to use the API anyway
    try {
        $apiResponse = $apiClient->makeRequest('POST', '/getuser', ['nick' => $query]);
        
        
        $players = [];
        if (is_array($apiResponse) && !empty($apiResponse)) {
            // Check if it's a single result wrapped in an array or multiple results
            if (isset($apiResponse['name'])) {
                // Single result returned as object
                $players[] = [
                    'ucid' => $apiResponse['ucid'] ?? null,
                    'name' => htmlspecialchars($apiResponse['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                    'last_seen' => $apiResponse['last_seen'] ?? null
                ];
            } else {
                // Multiple results
                foreach ($apiResponse as $player) {
                    // Skip if not an array/object
                    if (!is_array($player)) {
                        continue;
                    }
                    
                    $name = $player['name'] ?? $player['player_name'] ?? $player['nick'] ?? '';
                    if ($name) {
                        $players[] = [
                            'ucid' => $player['ucid'] ?? $player['player_ucid'] ?? null,
                            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                            'last_seen' => $player['last_seen'] ?? null
                        ];
                    }
                }
            }
        }
        
        // Always return valid structure
        echo json_encode([
            'results' => $players,
            'count' => count($players),
            'source' => 'api',
            'error' => count($players) === 0 ? 'No players found' : null
        ]);
        
    } catch (Exception $apiError) {
        // The /getuser endpoint is broken - return informative error
        echo json_encode([
            'error' => 'Player search is currently unavailable',
            'message' => 'The DCSServerBot /getuser endpoint is returning errors. This is a known issue with some DCSServerBot installations.',
            'results' => [],
            'count' => 0,
            'source' => 'api'
        ]);
    }
    
} catch (Exception $e) {
    
    echo json_encode([
        'error' => 'Search service error',
        'results' => [],
        'count' => 0,
        'source' => 'api'
    ]);
}
