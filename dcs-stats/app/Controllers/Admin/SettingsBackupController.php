<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\SettingsBackupPageService;

final class SettingsBackupController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'site-config/demo_helpers.php',
            'language.php',
            'site_features.php',
            'site_metadata.php',
        ]);

        $currentAdmin = $this->requirePermission('manage_features');
        $settingsBackupService = new SettingsBackupPageService();
        $settingsBackupState = $settingsBackupService->state($currentAdmin);

        $this->render('settings_backup.php', $settingsBackupState);
    }
}
