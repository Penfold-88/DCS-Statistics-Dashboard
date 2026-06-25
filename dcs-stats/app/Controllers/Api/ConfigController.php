<?php

namespace DcsStats\Controllers\Api;

use DcsStats\Core\ApiResponse;
use DcsStats\Services\Api\PublicApiConfigService;

final class ConfigController
{
    public function show(): void
    {
        ApiResponse::json(
            (new PublicApiConfigService())->getBrowserConfig(),
            200,
            ['Cache-Control' => 'no-store']
        );
    }

    public function leaderboardClient(): void
    {
        ApiResponse::json(
            (new PublicApiConfigService())->getLeaderboardClientConfig(),
            200,
            ['Cache-Control' => 'no-store']
        );
    }
}
