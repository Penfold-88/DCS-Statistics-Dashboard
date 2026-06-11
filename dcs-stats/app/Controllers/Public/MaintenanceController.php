<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\View;
use DcsStats\Services\MaintenancePageService;

final class MaintenanceController
{
    public function show(): void
    {
        require_once DCS_ROOT_PATH . '/config_path.php';
        require_once DCS_ROOT_PATH . '/language.php';

        $state = (new MaintenancePageService())->state();

        if (!defined('MAINTENANCE_OVERRIDE') && (!$state['enabled'] || $state['allowed'])) {
            header('Location: index.php');
            return;
        }

        http_response_code(503);
        View::render('Public/maintenance.php', $state);
    }
}
