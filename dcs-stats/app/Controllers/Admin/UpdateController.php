<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\UpdatePageService;

final class UpdateController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'language.php',
        ]);

        $currentAdmin = $this->requirePermission('manage_updates');
        $updatePageState = (new UpdatePageService())->state($currentAdmin);

        $this->render('update.php', $updatePageState);
    }
}
