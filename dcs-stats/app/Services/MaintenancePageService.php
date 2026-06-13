<?php

namespace DcsStats\Services;

final class MaintenancePageService
{
    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::language();

        $maintenance = $this->config();

        return [
            'allowed' => $this->isAllowed($maintenance),
            'customThemeExists' => file_exists(DCS_ROOT_PATH . '/custom_theme.css'),
            'enabled' => !empty($maintenance['enabled']),
            'maintenance' => $maintenance,
        ];
    }

    private function config(): array
    {
        $maintenanceFile = DCS_ROOT_PATH . '/site-config/data/maintenance.json';
        $maintenance = ['enabled' => false, 'ip_whitelist' => []];
        if (!file_exists($maintenanceFile)) {
            return $maintenance;
        }

        $data = json_decode((string)file_get_contents($maintenanceFile), true);
        return is_array($data) ? array_merge($maintenance, $data) : $maintenance;
    }

    private function isAllowed(array $maintenance): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return in_array($ip, $maintenance['ip_whitelist'] ?? [], true);
    }
}
