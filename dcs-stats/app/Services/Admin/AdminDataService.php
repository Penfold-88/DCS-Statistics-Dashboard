<?php

namespace DcsStats\Services\Admin;

final class AdminDataService
{
    public function players($search = null, $limit = null, int $offset = 0): array
    {
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

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
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

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

    public function playerBans(bool $activeOnly = true): array
    {
        $bans = json_decode((string)file_get_contents(ADMIN_BANS_FILE), true) ?: [];

        if ($activeOnly) {
            $bans = array_filter($bans, function ($ban) {
                if (!$ban['is_active']) {
                    return false;
                }

                if (!$ban['expires_at']) {
                    return true;
                }

                return strtotime($ban['expires_at']) > time();
            });
        }

        return array_values($bans);
    }

    public function playerIsBanned($ucid): bool
    {
        foreach ($this->playerBans(true) as $ban) {
            if (($ban['player_ucid'] ?? '') === $ucid) {
                return true;
            }
        }

        return false;
    }

    public function exportCsv($data, string $filename = 'export.csv'): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    public function exportJson($data, string $filename = 'export.json'): void
    {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
