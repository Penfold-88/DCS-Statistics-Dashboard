<?php

namespace DcsStats\Services\Api;

final class PublicStatsFormatter
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

    public function formatLeaderboardPlayer(\DCSServerBotAPIClient $apiClient, array $player, int $index): array
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

    public function formatTopPilots(array $topPlayers): array
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

    public function formatTopSquadrons(array $squadrons): array
    {
        return array_map(function (array $squadron): array {
            return [
                'name' => $squadron['name'] ?? 'Unknown',
                'members' => isset($squadron['members']) && is_array($squadron['members']) ? count($squadron['members']) : 0,
                'credits' => $squadron['credits'] ?? 0,
            ];
        }, array_slice($squadrons, 0, 3));
    }

    public function emptyServerStats(string $error): array
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
