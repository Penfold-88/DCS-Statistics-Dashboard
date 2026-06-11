<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\MaintenancePageService;

final class MaintenanceController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language.php']);

        $currentAdmin = $this->requirePermission('manage_maintenance');
        $maintenanceState = (new MaintenancePageService())->state($currentAdmin);

        $this->render('maintenance.php', $maintenanceState);
    }
}
