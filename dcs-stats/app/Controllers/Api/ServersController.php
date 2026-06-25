<?php

namespace DcsStats\Controllers\Api;

use DcsStats\Core\ApiResponse;
use DcsStats\Services\Api\ServerListService;

final class ServersController
{
    public function index(): void
    {
        ini_set('display_errors', 0);
        error_reporting(0);

        \DcsStats\Core\SupportBootstrap::security();

        if (!checkRateLimit(60, 60)) {
            return;
        }

        try {
            $servers = (new ServerListService())->getServers();

            ApiResponse::json([
                'data' => $servers,
                'servers' => $servers,
                'source' => 'api',
                'generated' => date('c'),
            ]);
        } catch (\Exception $e) {
            ApiResponse::json([
                'error' => 'Service temporarily unavailable',
                'servers' => [],
                'data' => [],
                'source' => 'api',
            ]);
        }
    }
}

