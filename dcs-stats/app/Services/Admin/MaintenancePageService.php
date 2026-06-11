<?php

namespace DcsStats\Services\Admin;

final class MaintenancePageService
{
    public function state(array $currentAdmin): array
    {
        require_once DCS_ROOT_PATH . '/language.php';

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $maintenance = \loadMaintenanceConfig();
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$maintenance, $message, $messageType] = $this->handlePost($maintenance, $demoRestricted);
        }

        return [
            'currentIP' => $_SERVER['REMOTE_ADDR'] ?? '',
            'demoRestricted' => $demoRestricted,
            'maintenance' => $maintenance,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.maintenance.title'),
        ];
    }

    private function handlePost(array $maintenance, bool $demoRestricted): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [$maintenance, \dcs_t('admin.maintenance.csrf_invalid'), 'error'];
        }

        if ($demoRestricted) {
            return [$maintenance, \demoWriteLockMessage(), 'error'];
        }

        $action = $_POST['action'] ?? 'update';
        $maintenance['ip_whitelist'] = $maintenance['ip_whitelist'] ?? [];

        if ($action === 'remove_ip') {
            return $this->removeIp($maintenance);
        }

        return $this->updateMaintenance($maintenance);
    }

    private function removeIp(array $maintenance): array
    {
        $ipToRemove = trim($_POST['ip'] ?? '');
        $originalCount = count($maintenance['ip_whitelist']);
        $maintenance['ip_whitelist'] = array_values(array_filter(
            $maintenance['ip_whitelist'],
            static fn($ip) => $ip !== $ipToRemove
        ));

        if ($ipToRemove === '' || count($maintenance['ip_whitelist']) === $originalCount) {
            return [$maintenance, \dcs_t('admin.maintenance.ip_not_found'), 'error'];
        }

        \saveMaintenanceConfig($maintenance);
        \logAdminActivity('MAINTENANCE_IP_REMOVE', $_SESSION['admin_id'], 'settings', 'maintenance', ['ip' => $ipToRemove]);

        return [$maintenance, \dcs_t('admin.maintenance.ip_removed'), 'success'];
    }

    private function updateMaintenance(array $maintenance): array
    {
        $maintenance['enabled'] = isset($_POST['enabled']);
        $ip = trim($_POST['ip_address'] ?? '');

        if ($ip !== '') {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return [$maintenance, \dcs_t('admin.maintenance.invalid_ip'), 'error'];
            }

            if (!in_array($ip, $maintenance['ip_whitelist'])) {
                $maintenance['ip_whitelist'][] = $ip;
            }
        }

        \saveMaintenanceConfig($maintenance);
        \logAdminActivity('MAINTENANCE_UPDATE', $_SESSION['admin_id'], 'settings', 'maintenance', $maintenance);

        return [$maintenance, \dcs_t('admin.maintenance.save_success'), 'success'];
    }
}
