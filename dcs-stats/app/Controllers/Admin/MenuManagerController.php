<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\MenuManagerPageService;

final class MenuManagerController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language', 'siteFeatures']);
        $currentAdmin = $this->requirePermission('manage_features');
        $this->render('menu_manager.php', (new MenuManagerPageService())->state($currentAdmin));
    }
}
