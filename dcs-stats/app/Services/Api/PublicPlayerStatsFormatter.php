<?php

namespace DcsStats\Services\Api;

final class PublicPlayerStatsFormatter
{
    public function formatPlayerStats(string $playerName, array $apiStats, array $playerInfo, array $lastSession, array $moduleStats): array
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

    public function formatPlayerSearchResults(array $apiResponse): array
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
}
