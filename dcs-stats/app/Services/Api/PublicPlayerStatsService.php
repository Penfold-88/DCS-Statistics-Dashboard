<?php

namespace DcsStats\Services\Api;

final class PublicPlayerStatsService
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
}
