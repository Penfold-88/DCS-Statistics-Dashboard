<?php
/**
 * DCSServerBot REST API Client
 * 
 * This class handles all API interactions with DCSServerBot REST API
 * replacing the previous JSON file-based approach.
 */

if (!defined('DCS_ROOT_PATH')) {
    define('DCS_ROOT_PATH', dirname(__DIR__, 3));
}

\DcsStats\Core\SupportBootstrap::devMode();
\DcsStats\Core\SupportBootstrap::apiConfig();
\DcsStats\Core\SupportBootstrap::apiCache();

class DCSServerBotAPIClient {
    protected $apiBaseUrl;
    protected $apiKey;
    protected $timeout;
    protected $isDevMode;
    protected $config;
    private $mockDataProvider;
    private $httpClient;
    
    public function __construct($config = []) {
        $this->apiBaseUrl = $config['api_base_url'] ?? 'http://localhost:9876';
        $this->apiKey = $config['api_key'] ?? null;
        $this->timeout = $config['timeout'] ?? 30;
        $this->isDevMode = isDevMode();
        $this->config = $config;
        $this->mockDataProvider = new \DcsStats\Services\Api\DcsServerBotMockDataProvider();
        $this->httpClient = new \DcsStats\Services\Api\DcsServerBotHttpClient();
    }
    
    /**
     * Make a request to the API
     */
    public function makeRequest($method, $endpoint, $data = null) {
        // In dev mode, return mock data instead of making real API calls
        if ($this->isDevMode) {
            return $this->getMockData($endpoint, $data);
        }
        
        $response = $this->httpClient->request(
            $method,
            $this->apiBaseUrl,
            $endpoint,
            $data,
            $this->config,
            $this->apiKey,
            $this->timeout
        );

        return json_decode($response['body'], true);
    }

    public function getLeaderboard($what = 'kills', $limit = 10, $offset = 0, $order = 'desc') {
        return $this->makeRequest('GET', '/leaderboard', [
            'what' => $what,
            'limit' => $limit,
            'offset' => $offset,
            'order' => $order
        ]);
    }
    
    /**
     * Get user information by nickname
     * Returns an array of matching users
     */
    public function getUser($nickname) {
        if ($nickname) {
            // API expects 'nick' not 'nickname'
            // Returns an array of users that match this nick
            $users = $this->makeRequest('POST', '/getuser', ['nick' => $nickname]);
            // Return the first match if found
            return !empty($users) && is_array($users) ? $users[0] : null;
        }
    }
    
    /**
     * Get player statistics
     */
    public function getPlayerStats($nickname, $date = null) {
        if (!$nickname) {
            return null;
        }

        $date = $this->resolvePlayerDate($nickname, $date);
        
        // API expects 'nick' and the user's exact last seen date
        $data = [
            'nick' => $nickname,
            'date' => $date
        ];
        return $this->makeRequest('POST', '/stats', $data);
    }
    
    /**
     * Get top players by kills
     */
    public function getTopKills() {
        return $this->makeRequest('GET', '/topkills');
    }
    
    /**
     * Get top players by kill/death ratio
     */
    public function getTopKDR() {
        return $this->makeRequest('GET', '/topkdr');
    }

    public function getServerStats() {
        return $this->makeRequest('GET', '/serverstats');
    }

    public function getServerAttendance() {
        return $this->makeRequest('GET', '/server_attendance');
    }

    public function getPlayerInfo($nickname, $date = null) {
        if (!$nickname) {
            return null;
        }

        $data = ['nick' => $nickname];
        if ($date) {
            $data['date'] = $date;
        }

        return $this->makeRequest('POST', '/player_info', $data);
    }
    
    /**
     * Get missile probability of kill for a player
     */
    public function getWeaponPK($nickname, $date = null) {
        if (!$nickname) {
            return null;
        }

        $date = $this->resolvePlayerDate($nickname, $date);
        
        // API expects 'nick' and the user's exact last seen date
        $data = [
            'nick' => $nickname,
            'date' => $date
        ];
        return $this->makeRequest('POST', '/weaponpk', $data);
    }
    
    /**
     * Search for players (custom implementation since not in API)
     * This will need to be adjusted based on available API endpoints
     */
    public function searchPlayers($query) {
        // For now, we'll need to implement this differently
        // Perhaps by getting all players and filtering client-side
        // or requesting this feature in the API
        throw new Exception('Player search not yet implemented in API');
    }
    
    /**
     * Get credits/points for players (custom implementation)
     * This endpoint doesn't exist in the current API
     */
    public function getCredits() {
        $leaderboard = $this->getLeaderboard('credits', 50);
        return $leaderboard['items'] ?? [];
    }
    
    /**
     * Get squadron information (custom implementation)
     * This endpoint doesn't exist in the current API
     */
    public function getSquadrons() {
        return $this->makeRequest('GET', '/squadrons');
    }
    
    /**
     * Get server/instance information (custom implementation)
     * This endpoint doesn't exist in the current API
     */
    public function getServers() {
        return $this->makeRequest('GET', '/servers');
    }
    
    /**
     * Get mock data for development mode
     */
    private function getMockData($endpoint, $data = null) {
        return $this->mockDataProvider->get($endpoint, $data);
    }

    private function resolvePlayerDate($nickname, $date) {
        if ($date) {
            return $date;
        }

        try {
            $userData = $this->makeRequest('POST', '/getuser', ['nick' => $nickname]);
            if ($userData && is_array($userData) && isset($userData[0]['date'])) {
                return $userData[0]['date'];
            }

            throw new Exception('Unable to determine user last seen date');
        } catch (Exception $e) {
            throw new Exception('Failed to get user data: ' . $e->getMessage());
        }
    }
}

// Configuration loader
function loadAPIConfig() {
    $configResult = loadApiConfigWithFix();
    if (!empty($configResult['config']) && is_array($configResult['config'])) {
        return $configResult['config'];
    }
    
    // Default configuration
    return [
        'api_base_url' => getenv('DCSBOT_API_URL') ?: 'http://localhost:8080',
        'api_key' => getenv('DCSBOT_API_KEY') ?: null,
        'timeout' => 30
    ];
}

// Create global API client instance
$apiClient = new DCSServerBotAPIClient(loadAPIConfig());
