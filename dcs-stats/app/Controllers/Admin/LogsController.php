<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\LogsPageService;

final class LogsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot();

        $this->requirePermission('view_logs');

        $logsState = (new LogsPageService())->state();

        $this->render('logs.php', $logsState);
    }
}
