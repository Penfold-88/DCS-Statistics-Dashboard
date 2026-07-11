<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\FeatureSettingsPageService;

final class FeatureSettingsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'language',
            'siteFeatures',
        ]);

        $currentAdmin = $this->requirePermission('manage_features');
        $featureSettingsService = new FeatureSettingsPageService();
        $featureSettingsState = $featureSettingsService->state($currentAdmin);

        $this->render('settings.php', $featureSettingsState);
    }
}
