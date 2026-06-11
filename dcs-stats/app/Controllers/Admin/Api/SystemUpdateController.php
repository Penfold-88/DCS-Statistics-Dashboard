<?php

namespace DcsStats\Controllers\Admin\Api;

use DcsStats\Core\AdminAuth;
use DcsStats\Core\Csrf;
use DcsStats\Services\Admin\SystemUpdateService;

final class SystemUpdateController
{
    public function run(): void
    {
        AdminAuth::requirePermission('manage_updates');
        Csrf::requireValid();

        set_time_limit(0);
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');

        require_once DCS_ROOT_PATH . '/site-config/demo_helpers.php';
        if (isDemoRestricted()) {
            $this->logMessage(demoRestrictionMessage());
            return;
        }

        (new SystemUpdateService())->run(
            !empty($_POST['version']) ? (string)$_POST['version'] : null,
            function (string $message) {
                $this->logMessage($message);
            }
        );
    }

    private function logMessage(string $message): void
    {
        echo $message . "\n";
        @ob_flush();
        flush();
    }
}
