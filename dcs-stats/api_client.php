<?php
/**
 * DCSServerBot REST API Client
 * 
 * This class handles all API interactions with DCSServerBot REST API
 * replacing the previous JSON file-based approach.
 */

require_once __DIR__ . '/dev_mode.php';

class DCSServerBotAPIClient {
    protected $apiBaseUrl;
    protected $apiKey;
    protected $timeout;
    protected $isDevMode;
    
    public function __construct($config = []) {
        $this->apiBaseUrl = $config['api_base_url'] ?? 'http://localhost:9876';
        $this->apiKey = $config['api_key'] ?? null;
        $this->timeout = $config['timeout'] ?? 30;
        $this->isDevMode = isDevMode();
    }
    
    /**
     * Make a request to the API
     */
    public function makeRequest($method, $endpoint, $data = null) {
        // In dev mode, return mock data instead of making real API calls
        if ($this->isDevMode) {
            return $this->getMockData($endpoint, $data);
        }
        
        $url = $this->apiBaseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // Add headers
        $headers = [
            'Accept: */*'
        ];
        
        if ($this->apiKey) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }
        
        // Set method and data
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                // Use form-urlencoded for POST data
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            }
        } elseif ($method === 'GET') {
            if ($data) {
                $url .= '?' . http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        
        // Set headers after determining content type
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('API request failed: ' . $error);
        }
        
        if ($httpCode >= 400) {
            throw new Exception('API returned error code: ' . $httpCode);
        }
        
        return json_decode($response, true);
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

        // If no date provided, we need to get the user's last seen date first
        if (!$date) {
            try {
                $userData = $this->makeRequest('POST', '/getuser', ['nick' => $nickname]);
                if ($userData && is_array($userData) && isset($userData[0]['date'])) {
                    $date = $userData[0]['date'];
                } else {
                    // If we can't get user data, stats will likely fail
                    throw new Exception('Unable to determine user last seen date');
                }
            } catch (Exception $e) {
                throw new Exception('Failed to get user data: ' . $e->getMessage());
            }
        }
        
        // API expects 'nick' and the user's exact last seen date
        $data = [
            'nick' => $nickname,
            'date' => $date
        ];
        return $this->makeRequest('POST', '/player_info', $data);
    }
    
    /**
     * Get top players by kills
     */
    public function getTopKills() {
        return $this->makeRequest('POST', '/topkills');
    }
    
    /**
     * Get top players by kill/death ratio
     */
    public function getTopKDR() {
        return $this->makeRequest('POST', '/topkdr');
    }
    
    /**
     * Get missile probability of kill for a player
     */
    public function getWeaponPK($nickname, $date = null) {
        if (!$nickname) {
            return null;
        }

        // If no date provided, we need to get the user's last seen date first
        if (!$date) {
            try {
                $userData = $this->makeRequest('POST', '/getuser', ['nick' => $nickname]);
                if ($userData && is_array($userData) && isset($userData[0]['date'])) {
                    $date = $userData[0]['date'];
                } else {
                    // If we can't get user data, stats will likely fail
                    throw new Exception('Unable to determine user last seen date');
                }
            } catch (Exception $e) {
                throw new Exception('Failed to get user data: ' . $e->getMessage());
            }
        }
        
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
        // This method is deprecated - use makeRequest directly
        // See search_players_api.php for the actual implementation
        return $this->makeRequest('POST', '/getuser', ['nick' => $query]);
    }
    
    /**
     * Get credits/points for players (custom implementation)
     * This endpoint doesn't exist in the current API
     */
    public function getCredits() {
        // This method is deprecated - use makeRequest directly
        // See get_credits_api.php for the actual implementation
        return $this->makeRequest('POST', '/credits', []);
    }
    
    /**
     * Get squadron information (custom implementation)
     * This endpoint doesn't exist in the current API
     */
    public function getSquadrons() {
        // This method is deprecated - use makeRequest directly
        // See get_squadrons_api.php for the actual implementation
        return $this->makeRequest('GET', '/squadrons');
    }
    
    /**
     * Get server/instance information (custom implementation)
     * This endpoint doesn't exist in the current API
     */
    public function getServers() {
        // This method is deprecated - use makeRequest directly
        // See get_servers_api.php for the actual implementation
        return $this->makeRequest('GET', '/servers');
    }
    
    /**
     * Get mock data for development mode
     */
    private function getMockData($endpoint, $data = null) {
        // Remove query parameters from endpoint for matching
        $endpoint = strtok($endpoint, '?');
        
        switch ($endpoint) {
            case '/getuser':
                return [[
                    'nick' => $data['nick'] ?? 'TestPilot',
                    'date' => date('Y-m-d H:i:s'),
                    'userid' => '123456789',
                    'ucid' => 'abc-def-ghi-jkl',
                    'ipaddr' => '192.168.1.100'
                ]];
                
            case '/player_info':
                return [
                    'nick' => $data['nick'] ?? 'TestPilot',
                    'kills' => rand(10, 100),
                    'deaths' => rand(5, 50),
                    'kd' => round(rand(50, 300) / 100, 2),
                    'pvp_kills' => rand(5, 50),
                    'pvp_deaths' => rand(2, 25),
                    'ejections' => rand(0, 10),
                    'crashes' => rand(0, 5),
                    'teamkills' => rand(0, 3),
                    'flighttime' => rand(1000, 10000),
                    'last_seen' => date('Y-m-d H:i:s')
                ];
                
            case '/topkills':
                $players = [];
                for ($i = 1; $i <= 20; $i++) {
                    $players[] = [
                        'nick' => 'Pilot_' . $i,
                        'kills' => 100 - ($i * 4),
                        'deaths' => rand(10, 50),
                        'kd' => round((100 - ($i * 4)) / rand(10, 50), 2),
                        'pvp_kills' => 50 - ($i * 2),
                        'server' => 'Dev Server'
                    ];
                }
                return $players;
                
            case '/topkdr':
                $players = [];
                for ($i = 1; $i <= 20; $i++) {
                    $players[] = [
                        'nick' => 'Ace_' . $i,
                        'kills' => rand(50, 100),
                        'deaths' => rand(10, 30),
                        'kd' => round(5.0 - ($i * 0.2), 2),
                        'pvp_kills' => rand(25, 50),
                        'server' => 'Dev Server'
                    ];
                }
                return $players;
                
            case '/credits':
                $players = [];
                for ($i = 1; $i <= 20; $i++) {
                    $players[] = [
                        'nick' => 'Rich_' . $i,
                        'credits' => 10000 - ($i * 400),
                        'server' => 'Dev Server'
                    ];
                }
                return $players;
                
            case '/servers':
                return [
                    [
                        'server_name' => 'Development Test Server',
                        'mission_name' => 'Caucasus - Training',
                        'players' => rand(5, 30),
                        'max_players' => 32,
                        'status' => 'online',
                        'uptime' => rand(1000, 10000)
                    ]
                ];
                
            case '/squadrons':
                return [
                    [
                        'name' => 'Test Squadron Alpha',
                        'tag' => 'TSA',
                        'members' => rand(10, 30),
                        'created' => date('Y-m-d', strtotime('-1 year'))
                    ],
                    [
                        'name' => 'Dev Squadron Beta',
                        'tag' => 'DSB',
                        'members' => rand(5, 20),
                        'created' => date('Y-m-d', strtotime('-6 months'))
                    ]
                ];
                
            case '/weaponpk':
                return [
                    'AIM-120C' => ['shots' => rand(20, 50), 'hits' => rand(10, 25), 'pk' => rand(40, 80)],
                    'AIM-9X' => ['shots' => rand(10, 30), 'hits' => rand(8, 25), 'pk' => rand(60, 90)],
                    'R-77' => ['shots' => rand(15, 40), 'hits' => rand(8, 20), 'pk' => rand(35, 75)]
                ];
                
            default:
                // Generic response for unknown endpoints
                return [
                    'status' => 'success',
                    'message' => 'Dev mode - mock data',
                    'endpoint' => $endpoint,
                    'data' => []
                ];
        }
    }
}

// Configuration loader
function loadAPIConfig() {
    $configFile = __DIR__ . '/api_config.json';
    if (file_exists($configFile)) {
        return json_decode(file_get_contents($configFile), true);
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