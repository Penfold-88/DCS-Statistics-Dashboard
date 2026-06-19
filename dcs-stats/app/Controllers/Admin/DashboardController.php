<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\AdminDashboardPageService;

final class DashboardController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'siteFeatures',
            'installCheckin',
            'apiConfig',
            'language',
        ]);

        $this->requirePermission('view_dashboard');

        $dashboardState = (new AdminDashboardPageService())->state();

        $this->render('dashboard.php', $dashboardState);
    }
}
