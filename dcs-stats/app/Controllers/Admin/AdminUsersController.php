<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\AdminUsersPageService;

final class AdminUsersController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language.php']);

        $currentAdmin = $this->requirePermission('manage_admins');
        $adminUsersService = new AdminUsersPageService();
        $adminUsersState = $adminUsersService->state($currentAdmin);

        $this->render('admins.php', $adminUsersState);
    }
}
