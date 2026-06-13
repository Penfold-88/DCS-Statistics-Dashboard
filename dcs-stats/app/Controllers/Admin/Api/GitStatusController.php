<?php

namespace DcsStats\Controllers\Admin\Api;

use DcsStats\Core\AdminAuth;
use DcsStats\Core\ApiResponse;
use DcsStats\Services\Admin\GitStatusService;

final class GitStatusController
{
    public function show(): void
    {
        AdminAuth::requirePermission('manage_updates');

        \DcsStats\Core\SupportBootstrap::devMode();
        if (!isDevMode()) {
            ApiResponse::json([
                'success' => false,
                'error' => 'Not in development mode',
            ]);
            return;
        }

        ApiResponse::json((new GitStatusService())->getStatus());
    }
}

