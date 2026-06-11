<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\AdminDashboardPageService;

final class DashboardController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'site-config/update_channel.php',
            'site-config/version_tracker.php',
            'site_features.php',
            'install_checkin.php',
            'api_config_helper.php',
            'language.php',
        ]);

        $this->requirePermission('view_dashboard');

        $dashboardState = (new AdminDashboardPageService())->state();

        $this->render('dashboard.php', $dashboardState);
    }
}
