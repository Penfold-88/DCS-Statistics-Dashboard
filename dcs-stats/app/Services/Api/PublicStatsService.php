<?php

namespace DcsStats\Services\Api;

final class PublicStatsService
{
    private PublicStatsFormatter $formatter;
    private PublicPlayerStatsService $players;
    private PublicLeaderboardService $leaderboard;
    private PublicSquadronStatsService $squadrons;
    private PublicServerStatsService $servers;

    public function __construct(?PublicStatsFormatter $formatter = null)
    {
        $this->formatter = $formatter ?? new PublicStatsFormatter();
        $this->players = new PublicPlayerStatsService($this->formatter);
        $this->leaderboard = new PublicLeaderboardService($this->formatter);
        $this->squadrons = new PublicSquadronStatsService();
        $this->servers = new PublicServerStatsService($this->formatter);
    }

    public function getPlayerStats(string $rawPlayerName, ?string $playerDate): array
    {
        return $this->players->getPlayerStats($rawPlayerName, $playerDate);
    }

    public function getLeaderboard(string $sortBy, int $limit): array
    {
        return $this->leaderboard->getLeaderboard($sortBy, $limit);
    }

    public function getSquadrons(): array
    {
        return $this->squadrons->getSquadrons();
    }

    public function getMissionStats(): array
    {
        return $this->leaderboard->getMissionStats();
    }

    public function getCredits(): array
    {
        return $this->leaderboard->getCredits();
    }

    public function getSquadronMembers(array $input): array
    {
        return $this->squadrons->getSquadronMembers($input);
    }

    public function getSquadronCredits(array $input): array
    {
        return $this->squadrons->getSquadronCredits($input);
    }

    public function searchPlayers(string $rawQuery): array
    {
        return $this->players->searchPlayers($rawQuery);
    }

    public function getServerStats(): array
    {
        return $this->servers->getServerStats();
    }
}
