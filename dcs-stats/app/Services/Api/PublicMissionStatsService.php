<?php

namespace DcsStats\Services\Api;

final class PublicMissionStatsService
{
    public function get(): array
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
}
