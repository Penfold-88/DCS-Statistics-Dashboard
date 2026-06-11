<?php

namespace DcsStats\Controllers\Admin\Api;

use DcsStats\Core\AdminAuth;
use DcsStats\Services\Admin\UpdateCheckService;

final class UpdateCheckController
{
    public function show(): void
    {
        AdminAuth::requirePermission('manage_updates');

        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-cache');

        echo (new UpdateCheckService())->buildReport();
    }
}

