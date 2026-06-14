<?php

namespace DcsStats\Services\Api;

final class PublicStatsService
{
    private PublicStatsFormatter $formatter;

    public function __construct(?PublicStatsFormatter $formatter = null)
    {
        $this->formatter = $formatter ?? new PublicStatsFormatter();
    }

    public function getPlayerStats(string $rawPlayerName, ?string $playerDate): array
    {
        \DcsStats\Core\SupportBootstrap::security();
        \DcsStats\Core\SupportBootstrap::apiClient();

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
                'data' => $this->formatter->formatPlayerStats($playerName, $apiStats, $playerInfo ?? [], $lastSession, $moduleStats),
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
        \DcsStats\Core\SupportBootstrap::apiClient();
        \DcsStats\Core\SupportBootstrap::apiConfig();

        try {
            $config = loadApiConfigWithFix()['config'];
            $apiClient = new \DCSServerBotAPIClient($config);
            $limit = max(1, min(100, $limit));
            $leaderboard = $apiClient->getLeaderboard($sortBy, $limit);
            $topPlayers = $leaderboard['items'] ?? [];
            $stats = [];

            foreach ($topPlayers as $index => $player) {
                $stats[] = $this->formatter->formatLeaderboardPlayer($apiClient, $player, $index);
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
        \DcsStats\Core\SupportBootstrap::apiClient();
        \DcsStats\Core\SupportBootstrap::apiConfig();

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
        \DcsStats\Core\SupportBootstrap::apiClient();

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
        \DcsStats\Core\SupportBootstrap::apiClient();

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
        \DcsStats\Core\SupportBootstrap::apiClient();

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
        \DcsStats\Core\SupportBootstrap::apiClient();

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
        \DcsStats\Core\SupportBootstrap::security();
        \DcsStats\Core\SupportBootstrap::apiClient();
        \DcsStats\Core\SupportBootstrap::apiConfig();

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
                $players = $this->formatter->formatPlayerSearchResults(is_array($apiResponse) ? $apiResponse : []);

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
        \DcsStats\Core\SupportBootstrap::apiClient();
        \DcsStats\Core\SupportBootstrap::apiConfig();

        $config = loadApiConfigWithFix()['config'];
        if (!$config || !$config['use_api']) {
            return $this->formatter->emptyServerStats('API not configured');
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
                'top5Pilots' => $this->formatter->formatTopPilots(is_array($topPlayers) ? $topPlayers : []),
                'top3Squadrons' => $this->formatter->formatTopSquadrons(is_array($squadrons) ? $squadrons : []),
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            return $this->formatter->emptyServerStats('Service temporarily unavailable');
        }
    }
}
