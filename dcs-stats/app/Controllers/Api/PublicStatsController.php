<?php

namespace DcsStats\Controllers\Api;

use DcsStats\Core\ApiResponse;
use DcsStats\Services\Api\PublicStatsService;

final class PublicStatsController
{
    public function playerStats(): void
    {
        $this->prepareJson();
        $this->loadSecurity();

        if (!checkRateLimit(30, 60)) {
            return;
        }

        ApiResponse::json((new PublicStatsService())->getPlayerStats(
            $_GET['name'] ?? '',
            $_GET['date'] ?? null
        ));
    }

    public function leaderboard(): void
    {
        $this->prepareJson();
        $this->loadSecurity();

        if (!checkRateLimit(120, 60)) {
            return;
        }

        ApiResponse::json((new PublicStatsService())->getLeaderboard(
            $_GET['sort'] ?? 'kills',
            (int)($_GET['limit'] ?? 10)
        ));
    }

    public function squadrons(): void
    {
        $this->prepareJson();
        $result = (new PublicStatsService())->getSquadrons();
        if ($result['error'] !== null) {
            http_response_code(500);
        }

        ApiResponse::json($result);
    }

    public function missionStats(): void
    {
        $this->prepareJson();
        $this->loadSecurity();

        if (!checkRateLimit(60, 60)) {
            return;
        }

        ApiResponse::json((new PublicStatsService())->getMissionStats());
    }

    public function credits(): void
    {
        $this->prepareJson();
        $this->loadSecurity();

        if (!checkRateLimit(60, 60)) {
            return;
        }

        ApiResponse::json((new PublicStatsService())->getCredits());
    }

    public function squadronMembers(): void
    {
        $this->prepareJson();

        ApiResponse::json((new PublicStatsService())->getSquadronMembers($this->requestInput()));
    }

    public function squadronCredits(): void
    {
        $this->prepareJson();

        ApiResponse::json((new PublicStatsService())->getSquadronCredits($this->requestInput()));
    }

    public function searchPlayers(): void
    {
        $this->prepareJson();
        $this->loadSecurity();

        if (!checkRateLimit(60, 60)) {
            return;
        }

        ApiResponse::json((new PublicStatsService())->searchPlayers($_GET['search'] ?? $_GET['q'] ?? ''));
    }

    public function serverStats(): void
    {
        $this->prepareJson();
        $this->loadSecurity();

        if (!checkRateLimit(60, 60)) {
            return;
        }

        ApiResponse::json((new PublicStatsService())->getServerStats());
    }

    private function prepareJson(): void
    {
        ini_set('display_errors', '0');
        error_reporting(0);
        header('X-Content-Type-Options: nosniff');
    }

    private function loadSecurity(): void
    {
        \DcsStats\Core\SupportBootstrap::security();
    }

    private function requestInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return is_array($input) ? $input : [];
    }
}
