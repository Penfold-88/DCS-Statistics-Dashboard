<?php

namespace DcsStats\Services\Api;

final class PublicServerStatsService
{
    private PublicStatsFormatter $formatter;

    public function __construct(?PublicStatsFormatter $formatter = null)
    {
        $this->formatter = $formatter ?? new PublicStatsFormatter();
    }

    public function getServerStats(): array
    {
        \DcsStats\Core\SupportBootstrap::apiClient();
        \DcsStats\Core\SupportBootstrap::apiConfig();

        $config = loadApiConfigWithFix()['config'];
        if (!$config || !$config['use_api']) {
            return $this->formatter->emptyServerStats('API not configured');
        }

        try {
            $apiClient = createEnhancedAPIClient();
            $stats = $apiClient->getServerStats();
            $attendance = [];

            try {
                $attendance = $apiClient->getServerAttendance();
            } catch (\Exception $e) {
                $attendance = [];
            }

            $leaderboard = $apiClient->getLeaderboard('kills', 5);
            $topPlayers = $leaderboard['items'] ?? $apiClient->getTopKills();
            $squadrons = [];
            try {
                $squadrons = $apiClient->getSquadrons();
            } catch (\Exception $e) {
                $squadrons = [];
            }

            return [
                'totalPlayers' => $stats['totalPlayers'] ?? ($attendance['unique_players_30d'] ?? 0),
                'totalPlaytime' => $stats['totalPlaytime'] ?? 0,
                'avgPlaytime' => $stats['avgPlaytime'] ?? 0,
                'activePlayers' => $stats['activePlayers'] ?? ($attendance['current_players'] ?? 0),
                'totalSorties' => $stats['totalSorties'] ?? ($attendance['total_sorties'] ?? 0),
                'totalKills' => $stats['totalKills'] ?? ($attendance['total_kills'] ?? 0),
                'totalDeaths' => $stats['totalDeaths'] ?? ($attendance['total_deaths'] ?? 0),
                'totalPvPKills' => $stats['totalPvPKills'] ?? ($attendance['total_pvp_kills'] ?? 0),
                'totalPvPDeaths' => $stats['totalPvPDeaths'] ?? ($attendance['total_pvp_deaths'] ?? 0),
                'activityLastWeek' => $stats['daily_players'] ?? ($attendance['daily_trend'] ?? []),
                'attendance' => $attendance,
                'top5Pilots' => $this->formatter->formatTopPilots(is_array($topPlayers) ? $topPlayers : []),
                'top3Squadrons' => $this->formatter->formatTopSquadrons(is_array($squadrons) ? $squadrons : []),
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            return $this->formatter->emptyServerStats('Service temporarily unavailable');
        }
    }
}
