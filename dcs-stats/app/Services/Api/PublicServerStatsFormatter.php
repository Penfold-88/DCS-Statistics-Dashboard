<?php

namespace DcsStats\Services\Api;

final class PublicServerStatsFormatter
{
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
