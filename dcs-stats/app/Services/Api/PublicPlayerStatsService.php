<?php

namespace DcsStats\Services\Api;

final class PublicPlayerStatsService
{
    private PublicPlayerProfileService $profileService;
    private PublicPlayerSearchService $searchService;

    public function __construct(
        ?PublicStatsFormatter $formatter = null,
        ?PublicPlayerProfileService $profileService = null,
        ?PublicPlayerSearchService $searchService = null
    ) {
        $formatter = $formatter ?? new PublicStatsFormatter();
        $this->profileService = $profileService ?? new PublicPlayerProfileService($formatter);
        $this->searchService = $searchService ?? new PublicPlayerSearchService($formatter);
    }

    public function getPlayerStats(string $rawPlayerName, ?string $playerDate): array
    {
        return $this->profileService->get($rawPlayerName, $playerDate);
    }

    public function searchPlayers(string $rawQuery): array
    {
        return $this->searchService->search($rawQuery);
    }
}
