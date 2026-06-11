<?php

namespace DcsStats\Services\Api;

final class PublicStatsService
{
    public function getPlayerStats(string $rawPlayerName, ?string $playerDate): array
    {
        require_once DCS_ROOT_PATH . '/security_functions.php';
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

        $playerName = validateInput($rawPlayerName, [
            'type' => 'player_name',
            'max_length' => 50,
            'min_length' => 1,
        ]);

        if ($playerName === false) {
            logSecurityEvent('INVALID_INPUT', 'Invalid player name format: ' . substr($rawPlayerName, 0, 20));
            return ['error' => 'Invalid player name format'];
        }

        if (!$playerName) {
            logSecurityEvent('INVALID_INPUT', 'Empty player name provided');
            return ['error' => 'Invalid request'];
        }

        try {
            $apiClient = createEnhancedAPIClient();
            $playerInfo = null;

            try {
                $playerInfo = $apiClient->getPlayerInfo($playerName, $playerDate);
            } catch (\Exception $e) {
                $playerInfo = null;
            }

            $apiStats = $playerInfo['overall'] ?? $apiClient->getPlayerStats($playerName, $playerDate);
            $lastSession = $playerInfo['last_session'] ?? [];
            $moduleStats = $playerInfo['module_stats'] ?? ($apiStats['killsByModule'] ?? []);

            if (!$apiStats || !is_array($apiStats)) {
                return [
                    'error' => 'Player not found',
                    'source' => 'api',
                    'timestamp' => date('c'),
                ];
            }

            return [
                'source' => 'api',
                'timestamp' => date('c'),
                'data' => $this->formatPlayerStats($playerName, $apiStats, $playerInfo ?? [], $lastSession, $moduleStats),
            ];
        } catch (\Exception $e) {
            logSecurityEvent('API_ERROR', 'Player stats API error: ' . $e->getMessage());
            return [
                'error' => 'Service temporarily unavailable',
                'message' => 'Unable to retrieve player statistics',
                'source' => 'api',
                'timestamp' => date('c'),
            ];
        }
    }

    public function getLeaderboard(string $sortBy, int $limit): array
    {
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';
        require_once DCS_ROOT_PATH . '/api_config_helper.php';

        try {
            $config = loadApiConfigWithFix()['config'];
            $apiClient = new \DCSServerBotAPIClient($config);
            $limit = max(1, min(100, $limit));
            $leaderboard = $apiClient->getLeaderboard($sortBy, $limit);
            $topPlayers = $leaderboard['items'] ?? [];
            $stats = [];

            foreach ($topPlayers as $index => $player) {
                $stats[] = $this->formatLeaderboardPlayer($apiClient, $player, $index);
            }

            return [
                'data' => $stats,
                'source' => 'api',
                'count' => count($stats),
                'total_count' => $leaderboard['total_count'] ?? count($stats),
                'generated' => date('c'),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Service temporarily unavailable',
                'data' => [],
                'source' => 'api',
                'count' => 0,
            ];
        }
    }

    public function getSquadrons(): array
    {
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';
        require_once DCS_ROOT_PATH . '/api_config_helper.php';

        $response = ['data' => [], 'error' => null];

        try {
            $apiConfig = loadApiConfigWithFix()['config'];
            if (!$apiConfig || !$apiConfig['use_api']) {
                throw new \Exception('API not configured or disabled. Please check API settings.');
            }

            $client = createEnhancedAPIClient();
            $squadrons = $client->request('/squadrons', null, 'GET');

            if ($squadrons && is_array($squadrons)) {
                $response['data'] = array_map(function (array $squadron): array {
                    return [
                        'name' => $squadron['name'] ?? '',
                        'description' => $squadron['description'] ?? '',
                        'image_url' => $squadron['image_url'] ?? '',
                        'locked' => $squadron['locked'] ?? false,
                        'role' => $squadron['role'] ?? '',
                        'member_count' => 0,
                        'total_credits' => 0,
                    ];
                }, $squadrons);
            } else {
                $response['error'] = 'No squadrons data available';
            }
        } catch (\Exception $e) {
            $response['error'] = 'Failed to fetch squadrons: ' . $e->getMessage();
        }

        return $response;
    }

    public function getMissionStats(): array
    {
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

        try {
            $client = createEnhancedAPIClient();
            $topKills = $client->request('/topkills', null, 'GET');

            if (!$topKills || !is_array($topKills)) {
                return [];
            }

            $stats = [];
            foreach ($topKills as $player) {
                $stats[] = [
                    'name' => htmlspecialchars($player['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'kills' => intval($player['kills'] ?? 0),
                    'deaths' => intval($player['deaths'] ?? 0),
                    'sorties' => intval($player['sorties'] ?? 0),
                    'missions' => intval($player['missions'] ?? 0),
                    'points' => intval($player['points'] ?? 0),
                ];
            }

            usort($stats, function ($a, $b) {
                return $b['points'] <=> $a['points'];
            });

            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getCredits(): array
    {
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

        $response = ['data' => [], 'error' => null];

        try {
            $client = createEnhancedAPIClient();
            $leaderboard = $client->request('/leaderboard?what=credits&limit=100', null, 'GET');
            $players = $leaderboard['items'] ?? [];

            if ($players && is_array($players)) {
                $response['data'] = array_map(function (array $player): array {
                    return [
                        'name' => $player['nick'] ?? 'Unknown',
                        'nick' => $player['nick'] ?? 'Unknown',
                        'credits' => $player['credits'] ?? 0,
                        'kills' => $player['kills'] ?? 0,
                        'deaths' => $player['deaths'] ?? 0,
                        'kdr' => $player['kdr'] ?? 0,
                    ];
                }, $players);
                $response['total_count'] = $leaderboard['total_count'] ?? count($players);
                $response['source'] = 'api';
            } else {
                $response['error'] = 'No credits data available';
            }
        } catch (\Exception $e) {
            $response['error'] = 'Failed to fetch credits: ' . $e->getMessage();
        }

        return $response;
    }

    public function getSquadronMembers(array $input): array
    {
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

        $response = ['data' => [], 'error' => null];

        try {
            $squadronName = $input['name'] ?? $_POST['name'] ?? '';
            if ($squadronName === '') {
                throw new \Exception('Squadron name is required');
            }

            $client = createEnhancedAPIClient();
            $members = $client->request('/squadron_members', ['name' => $squadronName]);

            if ($members) {
                $response['data'] = $members;
            } else {
                $response['error'] = 'No members data available for squadron: ' . $squadronName;
            }
        } catch (\Exception $e) {
            $response['error'] = 'Failed to fetch squadron members: ' . $e->getMessage();
        }

        return $response;
    }

    public function getSquadronCredits(array $input): array
    {
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

        $response = ['data' => [], 'error' => null];

        try {
            $squadronName = $input['name'] ?? $_POST['name'] ?? '';
            if ($squadronName === '') {
                throw new \Exception('Squadron name is required');
            }

            $client = createEnhancedAPIClient();
            $credits = $client->request('/squadron_credits', ['name' => $squadronName]);

            if ($credits) {
                $response['data'] = $credits;
            } else {
                $response['error'] = 'No credits data available for squadron: ' . $squadronName;
            }
        } catch (\Exception $e) {
            $response['error'] = 'Failed to fetch squadron credits: ' . $e->getMessage();
        }

        return $response;
    }

    public function searchPlayers(string $rawQuery): array
    {
        require_once DCS_ROOT_PATH . '/security_functions.php';
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';
        require_once DCS_ROOT_PATH . '/api_config_helper.php';

        $query = validateInput($rawQuery, [
            'type' => 'search_query',
            'max_length' => 50,
            'min_length' => 2,
        ]);

        if ($query === false) {
            return ['error' => 'Invalid search query'];
        }

        try {
            $config = loadApiConfigWithFix()['config'];
            $apiClient = new \DCSServerBotAPIClient($config);

            try {
                $apiResponse = $apiClient->makeRequest('POST', '/getuser', ['nick' => $query]);
                $players = $this->formatPlayerSearchResults(is_array($apiResponse) ? $apiResponse : []);

                return [
                    'results' => $players,
                    'count' => count($players),
                    'source' => 'api',
                    'error' => count($players) === 0 ? 'No players found' : null,
                ];
            } catch (\Exception $apiError) {
                return [
                    'error' => 'Player search is currently unavailable',
                    'message' => 'The DCSServerBot /getuser endpoint is returning errors. This is a known issue with some DCSServerBot installations.',
                    'results' => [],
                    'count' => 0,
                    'source' => 'api',
                ];
            }
        } catch (\Exception $e) {
            return [
                'error' => 'Search service error',
                'results' => [],
                'count' => 0,
                'source' => 'api',
            ];
        }
    }

    public function getServerStats(): array
    {
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';
        require_once DCS_ROOT_PATH . '/api_config_helper.php';

        $config = loadApiConfigWithFix()['config'];
        if (!$config || !$config['use_api']) {
            return $this->emptyServerStats('API not configured');
        }

        try {
            $apiClient = createEnhancedAPIClient();
            $stats = $apiClient->getServerStats();
            $attendance = [];

            try {
                $attendance = $apiClient->getServerAttendance();
            } catch (\Exception $e) {
                $attendance = [];
            }

            $leaderboard = $apiClient->getLeaderboard('kills', 5);
            $topPlayers = $leaderboard['items'] ?? $apiClient->getTopKills();
            $squadrons = [];
            try {
                $squadrons = $apiClient->getSquadrons();
            } catch (\Exception $e) {
                $squadrons = [];
            }

            return [
                'totalPlayers' => $stats['totalPlayers'] ?? ($attendance['unique_players_30d'] ?? 0),
                'totalPlaytime' => $stats['totalPlaytime'] ?? 0,
                'avgPlaytime' => $stats['avgPlaytime'] ?? 0,
                'activePlayers' => $stats['activePlayers'] ?? ($attendance['current_players'] ?? 0),
                'totalSorties' => $stats['totalSorties'] ?? ($attendance['total_sorties'] ?? 0),
                'totalKills' => $stats['totalKills'] ?? ($attendance['total_kills'] ?? 0),
                'totalDeaths' => $stats['totalDeaths'] ?? ($attendance['total_deaths'] ?? 0),
                'totalPvPKills' => $stats['totalPvPKills'] ?? ($attendance['total_pvp_kills'] ?? 0),
                'totalPvPDeaths' => $stats['totalPvPDeaths'] ?? ($attendance['total_pvp_deaths'] ?? 0),
                'activityLastWeek' => $stats['daily_players'] ?? ($attendance['daily_trend'] ?? []),
                'attendance' => $attendance,
                'top5Pilots' => $this->formatTopPilots(is_array($topPlayers) ? $topPlayers : []),
                'top3Squadrons' => $this->formatTopSquadrons(is_array($squadrons) ? $squadrons : []),
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            return $this->emptyServerStats('Service temporarily unavailable');
        }
    }

    private function formatPlayerStats(string $playerName, array $apiStats, array $playerInfo, array $lastSession, array $moduleStats): array
    {
        $stats = [
            'nick' => htmlspecialchars($playerName, ENT_QUOTES, 'UTF-8'),
            'kills' => $apiStats['kills'] ?? 0,
            'deaths' => $apiStats['deaths'] ?? 0,
            'kdr' => $apiStats['kdr'] ?? 0,
            'kd_ratio' => $apiStats['kdr'] ?? 0,
            'kills_pvp' => $apiStats['kills_pvp'] ?? 0,
            'deaths_pvp' => $apiStats['deaths_pvp'] ?? 0,
            'kdr_pvp' => $apiStats['kdr_pvp'] ?? 0,
            'teamkills' => $apiStats['teamkills'] ?? 0,
            'takeoffs' => $apiStats['takeoffs'] ?? 0,
            'landings' => $apiStats['landings'] ?? 0,
            'crashes' => $apiStats['crashes'] ?? 0,
            'ejections' => $apiStats['ejections'] ?? 0,
            'playtime' => $apiStats['playtime'] ?? 0,
            'sorties' => $apiStats['sorties'] ?? 0,
            'lastSessionKills' => $apiStats['lastSessionKills'] ?? 0,
            'lastSessionDeaths' => $apiStats['lastSessionDeaths'] ?? 0,
            'killsByModule' => $apiStats['killsByModule'] ?? [],
            'kdrByModule' => $apiStats['kdrByModule'] ?? [],
        ];

        if (!empty($lastSession)) {
            $stats['last_session_kills'] = $lastSession['kills'] ?? 0;
            $stats['last_session_deaths'] = $lastSession['deaths'] ?? 0;
            $stats['last_session_takeoffs'] = $lastSession['takeoffs'] ?? 0;
            $stats['last_session_landings'] = $lastSession['landings'] ?? 0;
        }

        if (isset($playerInfo['credits']) && is_array($playerInfo['credits'])) {
            $stats['credits'] = $playerInfo['credits']['credits'] ?? 0;
            $stats['rank'] = $playerInfo['credits']['rank'] ?? null;
            $stats['badge'] = $playerInfo['credits']['badge'] ?? null;
            $stats['campaign'] = $playerInfo['credits']['name'] ?? null;
        }

        if (!empty($playerInfo['current_server'])) {
            $stats['current_server'] = $playerInfo['current_server'];
        }

        if (!empty($playerInfo['squadrons']) && is_array($playerInfo['squadrons'])) {
            $stats['squadrons'] = $playerInfo['squadrons'];
            $stats['squadron'] = $playerInfo['squadrons'][0]['name'] ?? null;
        }

        if (!empty($moduleStats) && is_array($moduleStats)) {
            $stats['killsByModule'] = $moduleStats;
            $stats['aircraftUsage'] = array_map(function ($module): array {
                return [
                    'name' => $module['module'] ?? 'Unknown',
                    'count' => $module['kills'] ?? 0,
                ];
            }, $moduleStats);
        }

        $stats['most_used_aircraft'] = 'Unknown';
        if (!empty($stats['killsByModule'])) {
            $mostUsedModule = null;
            foreach ($stats['killsByModule'] as $module) {
                if (!is_array($module)) {
                    continue;
                }
                if ($mostUsedModule === null || ($module['kills'] ?? 0) > ($mostUsedModule['kills'] ?? 0)) {
                    $mostUsedModule = $module;
                }
            }
            $stats['most_used_aircraft'] = $mostUsedModule['module'] ?? 'Unknown';
        }

        return $stats;
    }

    private function formatLeaderboardPlayer(\DCSServerBotAPIClient $apiClient, array $player, int $index): array
    {
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
        } catch (\Exception $detailError) {
            $overall = [];
        }

        return [
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
            'most_used_aircraft' => $mostUsedAircraft,
        ];
    }

    private function formatPlayerSearchResults(array $apiResponse): array
    {
        if (isset($apiResponse['name'])) {
            return [[
                'ucid' => $apiResponse['ucid'] ?? null,
                'name' => htmlspecialchars($apiResponse['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                'last_seen' => $apiResponse['last_seen'] ?? null,
            ]];
        }

        $players = [];
        foreach ($apiResponse as $player) {
            if (!is_array($player)) {
                continue;
            }

            $name = $player['name'] ?? $player['player_name'] ?? $player['nick'] ?? '';
            if ($name) {
                $players[] = [
                    'ucid' => $player['ucid'] ?? $player['player_ucid'] ?? null,
                    'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                    'last_seen' => $player['last_seen'] ?? null,
                ];
            }
        }

        return $players;
    }

    private function formatTopPilots(array $topPlayers): array
    {
        return array_map(function (array $pilot): array {
            return [
                'name' => $pilot['nick'] ?? 'Unknown',
                'nick' => $pilot['nick'] ?? 'Unknown',
                'kills' => $pilot['kills'] ?? 0,
                'deaths' => $pilot['deaths'] ?? 0,
                'kdr' => $pilot['kdr'] ?? 0,
                'credits' => $pilot['credits'] ?? 0,
                'playtime' => $pilot['playtime'] ?? 0,
            ];
        }, array_slice($topPlayers, 0, 5));
    }

    private function formatTopSquadrons(array $squadrons): array
    {
        return array_map(function (array $squadron): array {
            return [
                'name' => $squadron['name'] ?? 'Unknown',
                'members' => isset($squadron['members']) && is_array($squadron['members']) ? count($squadron['members']) : 0,
                'credits' => $squadron['credits'] ?? 0,
            ];
        }, array_slice($squadrons, 0, 3));
    }

    private function emptyServerStats(string $error): array
    {
        return [
            'error' => $error,
            'totalPlayers' => 0,
            'totalKills' => 0,
            'totalDeaths' => 0,
            'top5Pilots' => [],
            'top3Squadrons' => [],
        ];
    }
}
