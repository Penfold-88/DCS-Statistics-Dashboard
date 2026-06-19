<?php

namespace DcsStats\Services\Api;

final class PublicLeaderboardService
{
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
            $allowedSorts = ['kills', 'deaths', 'kdr', 'kills_pvp', 'deaths_pvp', 'kdr_pvp', 'credits', 'playtime'];
            if (!in_array($sortBy, $allowedSorts, true)) {
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
}
