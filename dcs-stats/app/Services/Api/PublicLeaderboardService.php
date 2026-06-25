<?php

namespace DcsStats\Services\Api;

final class PublicLeaderboardService
{
    public const ALLOWED_SORTS = [
        'kills', 'deaths', 'kdr', 'kills_pvp', 'deaths_pvp', 'kdr_pvp', 'credits', 'playtime',
    ];

    private PublicStatsFormatter $formatter;
    private PublicMissionStatsService $missionStatsService;
    private PublicCreditsService $creditsService;

    public function __construct(
        ?PublicStatsFormatter $formatter = null,
        ?PublicMissionStatsService $missionStatsService = null,
        ?PublicCreditsService $creditsService = null
    ) {
        $this->formatter = $formatter ?? new PublicStatsFormatter();
        $this->missionStatsService = $missionStatsService ?? new PublicMissionStatsService();
        $this->creditsService = $creditsService ?? new PublicCreditsService();
    }

    public function getLeaderboard(string $sortBy, int $limit): array
    {
        \DcsStats\Core\SupportBootstrap::apiClient();
        \DcsStats\Core\SupportBootstrap::apiConfig();

        try {
            if (!self::isAllowedSort($sortBy)) {
                $sortBy = 'kills';
            }

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
        return $this->missionStatsService->get();
    }

    public function getCredits(): array
    {
        return $this->creditsService->get();
    }

    public static function isAllowedSort(string $sortBy): bool
    {
        return in_array($sortBy, self::ALLOWED_SORTS, true);
    }
}
