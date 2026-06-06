<?php
/**
 * Get Squadron Credits API Implementation
 * Fetches credits for a specific squadron from DCSServerBot API
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/api_client_enhanced.php';

// Initialize response
$response = ['data' => [], 'error' => null];

try {
    // Get squadron name from POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $squadronName = $input['name'] ?? $_POST['name'] ?? '';
    
    if (empty($squadronName)) {
        throw new Exception('Squadron name is required');
    }
    
    // Create API client
    $client = createEnhancedAPIClient();
    
    // Call the squadron_credits endpoint (POST request with name)
    $credits = $client->request('/squadron_credits', ['name' => $squadronName]);
    
    if ($credits) {
        $response['data'] = $credits;
    } else {
        $response['error'] = 'No credits data available for squadron: ' . $squadronName;
    }
    
} catch (Exception $e) {
    $response['error'] = 'Failed to fetch squadron credits: ' . $e->getMessage();
}

echo json_encode($response);
