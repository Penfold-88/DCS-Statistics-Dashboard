<?php

namespace DcsStats\Services\Admin;

final class PermissionsPageService
{
    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::language();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $message = '';
        $error = '';
        $permissionsFile = $this->configPath();
        $lsoPermissions = $this->loadPermissions($permissionsFile);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$lsoPermissions, $message, $error] = $this->handlePost($lsoPermissions, $permissionsFile, $demoRestricted);
        }

        return [
            'csrfToken' => \getCSRFToken(),
            'demoRestricted' => $demoRestricted,
            'error' => $error,
            'lsoPermissions' => $lsoPermissions,
            'message' => $message,
            'pageTitle' => \dcs_t('admin.permissions.title'),
            'permissionsFile' => $permissionsFile,
        ];
    }

    private function configPath(): string
    {
        $primaryPath = DCS_ROOT_PATH . '/site-config/data/lso_permissions.json';
        $primaryDir = dirname($primaryPath);

        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }

        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0700, true);
            @chmod($primaryDir, 0700);
            if (is_dir($primaryDir) && is_writable($primaryDir)) {
                return $primaryPath;
            }
        }

        $tempDir = sys_get_temp_dir() . '/dcs_stats';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }

        return $tempDir . '/lso_permissions.json';
    }

    private function defaultPermissions(): array
    {
        return [
            'view_dashboard' => ['enabled' => true, 'label' => \dcs_t('admin.permissions.view_dashboard'), 'description' => \dcs_t('admin.permissions.view_dashboard_desc')],
            'export_data' => ['enabled' => true, 'label' => \dcs_t('admin.permissions.export_data'), 'description' => \dcs_t('admin.permissions.export_data_desc')],
            'view_logs' => ['enabled' => true, 'label' => \dcs_t('admin.permissions.view_logs'), 'description' => \dcs_t('admin.permissions.view_logs_desc')],
            'manage_api' => ['enabled' => false, 'label' => \dcs_t('admin.permissions.manage_api'), 'description' => \dcs_t('admin.permissions.manage_api_desc')],
            'manage_features' => ['enabled' => false, 'label' => \dcs_t('admin.permissions.manage_features'), 'description' => \dcs_t('admin.permissions.manage_features_desc')],
            'manage_themes' => ['enabled' => false, 'label' => \dcs_t('admin.permissions.manage_themes'), 'description' => \dcs_t('admin.permissions.manage_themes_desc')],
            'manage_discord' => ['enabled' => false, 'label' => \dcs_t('admin.permissions.manage_discord'), 'description' => \dcs_t('admin.permissions.manage_discord_desc')],
            'manage_squadrons' => ['enabled' => false, 'label' => \dcs_t('admin.permissions.manage_squadrons'), 'description' => \dcs_t('admin.permissions.manage_squadrons_desc')],
            'manage_maintenance' => ['enabled' => false, 'label' => \dcs_t('admin.permissions.manage_maintenance'), 'description' => \dcs_t('admin.permissions.manage_maintenance_desc')],
            'manage_updates' => ['enabled' => false, 'label' => \dcs_t('admin.permissions.manage_updates'), 'description' => \dcs_t('admin.permissions.manage_updates_desc')],
            'change_settings' => ['enabled' => false, 'label' => \dcs_t('admin.permissions.change_settings'), 'description' => \dcs_t('admin.permissions.change_settings_desc')],
        ];
    }

    private function loadPermissions(string $permissionsFile): array
    {
        $lsoPermissions = $this->defaultPermissions();

        if (file_exists($permissionsFile)) {
            $saved = json_decode((string)file_get_contents($permissionsFile), true);
            if ($saved && is_array($saved)) {
                foreach ($saved as $key => $value) {
                    if (isset($lsoPermissions[$key])) {
                        $lsoPermissions[$key]['enabled'] = $value['enabled'] ?? false;
                    }
                }
            }
        }

        return $lsoPermissions;
    }

    private function handlePost(array $lsoPermissions, string $permissionsFile, bool $demoRestricted): array
    {
        if (!\verifyCSRFToken(\getRequestCSRFToken())) {
            return [$lsoPermissions, '', \dcs_t('admin.permissions.invalid_token')];
        }

        if ($demoRestricted) {
            return [$lsoPermissions, '', \demoRestrictionMessage()];
        }

        $enabledPerms = $_POST['permissions'] ?? [];

        foreach ($lsoPermissions as $key => &$perm) {
            $perm['enabled'] = in_array($key, $enabledPerms);
        }
        unset($perm);

        $result = @file_put_contents($permissionsFile, json_encode($lsoPermissions, JSON_PRETTY_PRINT));
        if ($result === false) {
            return [$lsoPermissions, '', \dcs_t('admin.permissions.save_failed')];
        }

        if (function_exists('logActivity')) {
            \logActivity('PERMISSIONS_UPDATE', 'Updated LSO group permissions');
        }

        $this->updateLsoPermissionsInConfig($lsoPermissions);

        return [$lsoPermissions, \dcs_t('admin.permissions.save_success'), ''];
    }

    private function updateLsoPermissionsInConfig(array $permissions): void
    {
        // Reserved for integration with the main auth permission backend.
    }
}
