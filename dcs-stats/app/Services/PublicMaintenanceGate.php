<?php

namespace DcsStats\Services;

final class PublicMaintenanceGate
{
    public function exitIfNeeded(): void
    {
        $maintenanceFile = DCS_ROOT_PATH . '/site-config/data/maintenance.json';
        if (!file_exists($maintenanceFile)) {
            return;
        }

        $maintenance = json_decode((string)file_get_contents($maintenanceFile), true);
        if (empty($maintenance['enabled'])) {
            return;
        }

        $allowed = $maintenance['ip_whitelist'] ?? [];
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (in_array($ip, $allowed)) {
            return;
        }

        if (!defined('MAINTENANCE_OVERRIDE')) {
            define('MAINTENANCE_OVERRIDE', true);
        }
        (new \DcsStats\Controllers\Public\MaintenanceController())->show();
        exit;
    }
}
