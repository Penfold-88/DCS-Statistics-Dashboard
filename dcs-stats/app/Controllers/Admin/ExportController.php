<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\ExportPageService;

final class ExportController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['site-config/demo_helpers.php']);

        $currentAdmin = $this->requirePermission('export_data');
        $exportState = (new ExportPageService())->state($currentAdmin);

        $this->render('export.php', $exportState);
    }
}
