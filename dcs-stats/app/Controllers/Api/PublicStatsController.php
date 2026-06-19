<?php

namespace DcsStats\Controllers\Api;

use DcsStats\Core\ApiResponse;
use DcsStats\Services\Api\PublicApiRequestGuard;
use DcsStats\Services\Api\PublicStatsService;

final class PublicStatsController
{
    private PublicStatsService $stats;
    private PublicApiRequestGuard $requestGuard;

    public function __construct(?PublicStatsService $stats = null, ?PublicApiRequestGuard $requestGuard = null)
    {
        $this->stats = $stats ?? new PublicStatsService();
        $this->requestGuard = $requestGuard ?? new PublicApiRequestGuard();
    }

    public function playerStats(): void
    {
        $this->requestGuard->prepareJson();

        if (!$this->requestGuard->allow(30, 60)) {
            return;
        }

        ApiResponse::json($this->stats->getPlayerStats(
            $_GET['name'] ?? '',
            $_GET['date'] ?? null
        ));
    }

    public function leaderboard(): void
    {
        $this->requestGuard->prepareJson();

        if (!$this->requestGuard->allow(120, 60)) {
            return;
        }

        ApiResponse::json($this->stats->getLeaderboard(
            $_GET['sort'] ?? 'kills',
            (int)($_GET['limit'] ?? 10)
        ));
    }

    public function squadrons(): void
    {
        $this->requestGuard->prepareJson();
        if (!$this->requestGuard->allow(60, 60)) {
            return;
        }

        $result = $this->stats->getSquadrons();
        if ($result['error'] !== null) {
            http_response_code(500);
        }

        ApiResponse::json($result);
    }

    public function missionStats(): void
    {
        $this->requestGuard->prepareJson();

        if (!$this->requestGuard->allow(60, 60)) {
            return;
        }

        ApiResponse::json($this->stats->getMissionStats());
    }

    public function credits(): void
    {
        $this->requestGuard->prepareJson();

        if (!$this->requestGuard->allow(60, 60)) {
            return;
        }

        ApiResponse::json($this->stats->getCredits());
    }

    public function squadronMembers(): void
    {
        $this->requestGuard->prepareJson();
        if (!$this->requestGuard->allow(60, 60)) {
            return;
        }

        ApiResponse::json($this->stats->getSquadronMembers($this->requestGuard->input()));
    }

    public function squadronCredits(): void
    {
        $this->requestGuard->prepareJson();
        if (!$this->requestGuard->allow(60, 60)) {
            return;
        }

        ApiResponse::json($this->stats->getSquadronCredits($this->requestGuard->input()));
    }

    public function searchPlayers(): void
    {
        $this->requestGuard->prepareJson();

        if (!$this->requestGuard->allow(60, 60)) {
            return;
        }

        ApiResponse::json($this->stats->searchPlayers($_GET['search'] ?? $_GET['q'] ?? ''));
    }

    public function serverStats(): void
    {
        $this->requestGuard->prepareJson();

        if (!$this->requestGuard->allow(60, 60)) {
            return;
        }

        ApiResponse::json($this->stats->getServerStats());
    }
}
