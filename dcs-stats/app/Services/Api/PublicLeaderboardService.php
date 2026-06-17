<?php

namespace DcsStats\Services\Api;

final class PublicLeaderboardService
{
    private PublicStatsFormatter $formatter;

    public function __construct(?PublicStatsFormatter $formatter = null)
    {
        $this->formatter = $formatter ?? new PublicStatsFormatter();
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
}
