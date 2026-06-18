<?php

namespace DcsStats\Services\Admin;

final class AdminPlayerDataService
{
    public function players($search = null, $limit = null, int $offset = 0): array
    {
        \DcsStats\Core\SupportBootstrap::apiClient();

        try {
            $client = \createEnhancedAPIClient();

            if ($search) {
                $searchUrl = '/search_players_api.php?search=' . urlencode((string)$search);
                if ($limit) {
                    $searchUrl .= '&limit=' . (int)$limit;
                }

                $response = @file_get_contents(DCS_ROOT_PATH . $searchUrl);
                if ($response) {
                    $data = json_decode($response, true);
                    return $data['results'] ?? [];
                }
            }

            $players = $client->request('/topkills', null, 'GET');

            if ($players && is_array($players)) {
                if ($offset || $limit) {
                    return array_slice($players, $offset, $limit);
                }

                return $players;
            }
        } catch (\Exception $e) {
            return [];
        }

        return [];
    }

    public function playerStats($ucid): array
    {
        \DcsStats\Core\SupportBootstrap::apiClient();

        $stats = [
            'ucid' => $ucid,
            'kills' => 0,
            'deaths' => 0,
            'flight_hours' => 0,
            'sorties' => 0,
            'last_seen' => null,
            'kd_ratio' => 0,
        ];

        try {
            $client = \createEnhancedAPIClient();
            $playerData = $client->request('/stats', ['ucid' => $ucid]);

            if ($playerData && is_array($playerData) && !empty($playerData)) {
                $player = is_array($playerData[0]) ? $playerData[0] : $playerData;

                $stats['kills'] = intval($player['kills'] ?? 0);
                $stats['deaths'] = intval($player['deaths'] ?? 0);
                $stats['sorties'] = intval($player['sorties'] ?? 0);
                $stats['flight_hours'] = floatval($player['flight_hours'] ?? 0);
                $stats['last_seen'] = $player['date'] ?? $player['last_seen'] ?? null;
                $stats['kd_ratio'] = $stats['deaths'] > 0
                    ? round($stats['kills'] / $stats['deaths'], 2)
                    : $stats['kills'];
            }
        } catch (\Exception $e) {
            return $stats;
        }

        return $stats;
    }
}
