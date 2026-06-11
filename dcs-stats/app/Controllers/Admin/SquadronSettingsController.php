<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\SquadronSettingsPageService;

final class SquadronSettingsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'language.php',
            'site_features.php',
        ]);

        $currentAdmin = $this->requirePermission('manage_squadrons');
        $squadronSettingsState = (new SquadronSettingsPageService())->state($currentAdmin);

        $this->render('squadron_settings.php', $squadronSettingsState);
    }
}
