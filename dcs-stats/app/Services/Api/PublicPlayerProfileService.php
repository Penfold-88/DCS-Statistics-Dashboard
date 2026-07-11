<?php

namespace DcsStats\Services\Api;

final class PublicPlayerProfileService
{
    private PublicStatsFormatter $formatter;

    public function __construct(?PublicStatsFormatter $formatter = null)
    {
        $this->formatter = $formatter ?? new PublicStatsFormatter();
    }

    public function get(string $rawPlayerName, ?string $playerDate): array
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
}
