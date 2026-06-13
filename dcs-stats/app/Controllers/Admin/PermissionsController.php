<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\PermissionsPageService;

final class PermissionsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'language.php',
        ]);

        $currentAdmin = $this->requirePermission('manage_permissions');
        $permissionsState = (new PermissionsPageService())->state($currentAdmin);

        $this->render('permissions.php', $permissionsState);
    }
}
