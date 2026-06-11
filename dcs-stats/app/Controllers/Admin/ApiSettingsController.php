<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\ApiSettingsPageService;

final class ApiSettingsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'site-config/demo_helpers.php',
            'api_config_helper.php',
            'api_cache.php',
            'language.php',
        ]);

        $currentAdmin = $this->requirePermission('manage_api');
        $apiSettingsState = (new ApiSettingsPageService())->state($currentAdmin);

        $this->render('api_settings.php', $apiSettingsState);
    }
}
