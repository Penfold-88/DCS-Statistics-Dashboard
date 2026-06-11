<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\ApiHealthPageService;

final class ApiHealthController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'api_config_helper.php',
            'language.php',
        ]);

        $this->requirePermission('manage_api');

        $apiHealthService = new ApiHealthPageService();
        $apiHealthState = $apiHealthService->state();

        $this->render('api_health.php', $apiHealthState);
    }
}
