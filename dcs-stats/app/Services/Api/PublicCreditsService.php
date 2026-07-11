<?php

namespace DcsStats\Services\Api;

final class PublicCreditsService
{
    public function get(): array
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
