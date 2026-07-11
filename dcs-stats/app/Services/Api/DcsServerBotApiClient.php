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
    private $playerClient;
    
    public function __construct(
        $config = [],
        $mockDataProvider = null,
        $httpClient = null,
        $playerClient = null
    ) {
        $this->apiBaseUrl = $config['api_base_url'] ?? 'http://localhost:9876';
        $this->apiKey = $config['api_key'] ?? null;
        $this->timeout = max(5, min(60, (int)($config['timeout'] ?? 30)));
        $this->isDevMode = isDevMode();
        $this->config = $config;
        $this->mockDataProvider = $mockDataProvider ?? new \DcsStats\Services\Api\DcsServerBotMockDataProvider();
        $this->httpClient = $httpClient ?? new \DcsStats\Services\Api\DcsServerBotHttpClient();
        $this->playerClient = $playerClient ?? new \DcsStats\Services\Api\DcsServerBotPlayerClient(
            function ($method, $endpoint, $data = null) {
                return $this->makeRequest($method, $endpoint, $data);
            }
        );
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
        return $this->playerClient->getUser($nickname);
    }
    
    /**
     * Get player statistics
     */
    public function getPlayerStats($nickname, $date = null) {
        return $this->playerClient->getPlayerStats($nickname, $date);
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
        return $this->playerClient->getPlayerInfo($nickname, $date);
    }
    
    /**
     * Get missile probability of kill for a player
     */
    public function getWeaponPK($nickname, $date = null) {
        return $this->playerClient->getWeaponPk($nickname, $date);
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

}

// Configuration loader
function loadAPIConfig() {
    return (new \DcsStats\Services\Api\DcsServerBotApiConfigLoader())->load();
}

// Create global API client instance
$apiClient = new DCSServerBotAPIClient(loadAPIConfig());
