<?php
/**
 * API Proxy Endpoint
 * Handles all API requests from JavaScript, bypassing CORS issues
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Load API configuration
require_once __DIR__ . '/api_config_helper.php';
require_once __DIR__ . '/api_cache.php';

$apiConfig = loadApiConfigWithFix()['config'];
if (!$apiConfig['use_api'] || empty($apiConfig['api_base_url'])) {
    http_response_code(503);
    echo json_encode(['error' => 'API not enabled']);
    exit;
}

// Get request parameters
$endpoint = $_GET['endpoint'] ?? '';
$method = strtoupper($_GET['method'] ?? 'GET');
$data = [];

$allowedEndpoints = [
    '/airbase' => ['GET'],
    '/airbase/atis' => ['GET'],
    '/airbase/warehouse' => ['GET'],
    '/airbases' => ['GET'],
    '/convertCoordinates' => ['GET'],
    '/credits' => ['POST'],
    '/current_server' => ['GET'],
    '/getuser' => ['POST'],
    '/highscore' => ['GET'],
    '/leaderboard' => ['GET'],
    '/mission/group/waypoints' => ['GET'],
    '/modulestats' => ['POST'],
    '/player_info' => ['POST'],
    '/player_squadrons' => ['POST'],
    '/server_attendance' => ['GET'],
    '/servers' => ['GET'],
    '/serverstats' => ['GET'],
    '/squadron_credits' => ['POST'],
    '/squadron_members' => ['POST'],
    '/squadrons' => ['GET'],
    '/stats' => ['POST'],
    '/topkdr' => ['GET'],
    '/topkills' => ['GET'],
    '/traps' => ['POST'],
    '/trueskill' => ['GET'],
    '/weaponpk' => ['POST']
];

$allowedQueryParams = [
    '/airbase' => ['server_name', 'name'],
    '/airbase/atis' => ['server_name', 'name'],
    '/airbase/warehouse' => ['server_name', 'name'],
    '/airbases' => ['server_name'],
    '/convertCoordinates' => ['server_name', 'lat', 'lon', 'mgrs', 'format'],
    '/current_server' => ['server_name'],
    '/highscore' => ['server_name', 'what', 'limit', 'offset'],
    '/leaderboard' => ['server', 'server_name', 'what', 'limit', 'offset', 'order'],
    '/mission/group/waypoints' => ['server_name', 'group_name'],
    '/server_attendance' => ['server', 'server_name'],
    '/servers' => ['server', 'server_name'],
    '/serverstats' => ['server', 'server_name'],
    '/squadrons' => ['server_name', 'limit', 'offset'],
    '/topkdr' => ['server', 'server_name', 'limit', 'offset'],
    '/topkills' => ['server', 'server_name', 'limit', 'offset'],
    '/trueskill' => ['server', 'server_name', 'limit', 'offset']
];

$allowedPostFields = [
    '/credits' => ['ucid', 'nick', 'name'],
    '/getuser' => ['ucid', 'nick', 'name', 'query', 'search', 'server_name'],
    '/modulestats' => ['ucid', 'nick', 'name', 'server_name', 'limit', 'offset'],
    '/player_info' => ['ucid', 'nick', 'name', 'server_name'],
    '/player_squadrons' => ['ucid', 'nick', 'name', 'server_name'],
    '/squadron_credits' => ['name', 'server_name'],
    '/squadron_members' => ['name', 'server_name'],
    '/stats' => ['ucid', 'nick', 'name', 'server_name'],
    '/traps' => ['nick', 'date', 'limit', 'offset', 'server_name'],
    '/weaponpk' => ['ucid', 'nick', 'name', 'server_name', 'limit', 'offset']
];

function filterAllowedParams($params, $allowedKeys) {
    $filtered = [];
    foreach ($params as $key => $value) {
        if (!in_array($key, $allowedKeys, true)) {
            continue;
        }
        if (is_array($value)) {
            continue;
        }
        $filtered[$key] = is_bool($value) ? ($value ? '1' : '0') : (string)$value;
    }
    return $filtered;
}

// Handle POST data
if ($method === 'POST') {
    // Get POST data from the request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? [];
}

// Validate endpoint
if (empty($endpoint)) {
    http_response_code(400);
    echo json_encode(['error' => 'Endpoint parameter required']);
    exit;
}

$endpointParts = parse_url($endpoint);
$endpointPath = $endpointParts['path'] ?? '';
if (!isset($allowedEndpoints[$endpointPath]) || !in_array($method, $allowedEndpoints[$endpointPath], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Endpoint or method not allowed']);
    exit;
}

$queryParams = [];
if (!empty($endpointParts['query'])) {
    parse_str($endpointParts['query'], $queryParams);
}
$queryParams = filterAllowedParams($queryParams, $allowedQueryParams[$endpointPath] ?? []);

if ($method === 'POST') {
    $data = filterAllowedParams($data, $allowedPostFields[$endpointPath] ?? []);
}

// Build full URL
$url = rtrim($apiConfig['api_base_url'], '/') . '/' . ltrim($endpointPath, '/');
if (!empty($queryParams)) {
    $url .= '?' . http_build_query($queryParams);
}

$cacheEndpoint = $endpointPath;
if (!empty($queryParams)) {
    $cacheEndpoint .= '?' . http_build_query($queryParams);
}
$cacheData = $method === 'POST' ? $data : null;
$cached = apiCacheRead($method, $apiConfig['api_base_url'], $cacheEndpoint, $cacheData, $apiConfig);
if ($cached !== null) {
    http_response_code((int)($cached['http_code'] ?? 200));
    header('X-DCS-API-Cache: HIT');
    echo $cached['body'];
    exit;
}

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, $apiConfig['timeout'] ?? 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
if (stripos($url, 'https://') === 0) {
    $verifySsl = filter_var($apiConfig['verify_ssl'] ?? true, FILTER_VALIDATE_BOOLEAN);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);
}

// Build headers — forward the API key to the upstream API if configured.
// DCSServerBot's REST API requires X-API-Key (Bearer is rejected).
$headers = [];
if (!empty($apiConfig['api_key'])) {
    $headers[] = 'X-API-Key: ' . $apiConfig['api_key'];
}

// Set method and data
if ($method === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);

    // Use form-urlencoded for POST data
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
    }
}

if (!empty($headers)) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
}

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Handle errors
if ($error) {
    http_response_code(502);
    echo json_encode(['error' => 'API request failed: ' . $error]);
    exit;
}

// Forward the HTTP status code
http_response_code($httpCode);
header('X-DCS-API-Cache: MISS');
apiCacheWrite($method, $apiConfig['api_base_url'], $cacheEndpoint, $cacheData, $apiConfig, $response, $httpCode);

// Return the response
echo $response;
