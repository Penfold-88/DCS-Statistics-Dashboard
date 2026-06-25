<?php

namespace DcsStats\Services\Api;

final class PublicStatsFormatter
{
    private PublicPlayerStatsFormatter $playerFormatter;
    private PublicServerStatsFormatter $serverFormatter;

    public function __construct(
        ?PublicPlayerStatsFormatter $playerFormatter = null,
        ?PublicServerStatsFormatter $serverFormatter = null
    ) {
        $this->playerFormatter = $playerFormatter ?? new PublicPlayerStatsFormatter();
        $this->serverFormatter = $serverFormatter ?? new PublicServerStatsFormatter();
    }

    public function formatPlayerStats(string $playerName, array $apiStats, array $playerInfo, array $lastSession, array $moduleStats): array
    {
        return $this->playerFormatter->formatPlayerStats($playerName, $apiStats, $playerInfo, $lastSession, $moduleStats);
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
        return $this->playerFormatter->formatPlayerSearchResults($apiResponse);
    }

    public function formatTopPilots(array $topPlayers): array
    {
        return $this->serverFormatter->formatTopPilots($topPlayers);
    }

    public function formatTopSquadrons(array $squadrons): array
    {
        return $this->serverFormatter->formatTopSquadrons($squadrons);
    }

    public function emptyServerStats(string $error): array
    {
        return $this->serverFormatter->emptyServerStats($error);
    }
}
