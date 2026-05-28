<?php
/**
 * Get Squadrons API Implementation
 * Fetches all squadrons from DCSServerBot API
 */

// Prevent any HTML error output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/api_client_enhanced.php';
require_once __DIR__ . '/api_config_helper.php';

// Initialize response
$response = ['data' => [], 'error' => null];

try {
    $apiConfig = loadApiConfigWithFix()['config'];
    if (!$apiConfig || !$apiConfig['use_api']) {
        throw new Exception('API not configured or disabled. Please check API settings.');
    }
    
    // Create API client
    $client = createEnhancedAPIClient();
    
    // Call the squadrons endpoint (GET request)
    $squadrons = $client->request('/squadrons', null, 'GET');
    
    if ($squadrons && is_array($squadrons)) {
        // Format squadron data properly
        $formattedSquadrons = [];
        foreach ($squadrons as $squadron) {
            $formattedSquadrons[] = [
                'name' => $squadron['name'] ?? '',
                'description' => $squadron['description'] ?? '',
                'image_url' => $squadron['image_url'] ?? '',
                'locked' => $squadron['locked'] ?? false,
                'role' => $squadron['role'] ?? '',
                'member_count' => 0, // Will be populated by frontend
                'total_credits' => 0 // Will be populated by frontend
            ];
        }
        $response['data'] = $formattedSquadrons;
    } else {
        $response['error'] = 'No squadrons data available';
    }
    
} catch (Exception $e) {
    $response['error'] = 'Failed to fetch squadrons: ' . $e->getMessage();
    http_response_code(500);
}

// Always return JSON even on error
echo json_encode($response, JSON_PRETTY_PRINT);
