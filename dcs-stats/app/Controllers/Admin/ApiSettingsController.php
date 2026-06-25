<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\ApiSettingsPageService;

final class ApiSettingsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'apiConfig',
            'apiCache',
            'language',
        ]);

        $currentAdmin = $this->requirePermission('manage_api');
        $apiSettingsState = (new ApiSettingsPageService())->state($currentAdmin);

        $this->render('api_settings.php', $apiSettingsState);
    }
}
