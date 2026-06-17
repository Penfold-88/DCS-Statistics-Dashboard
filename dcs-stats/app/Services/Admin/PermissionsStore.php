<?php

namespace DcsStats\Services\Admin;

final class PermissionsStore
{
    public function configPath(): string
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

    public function load(string $permissionsFile): array
    {
        $lsoPermissions = $this->defaults();

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

    public function save(string $permissionsFile, array $permissions): bool
    {
        return @file_put_contents($permissionsFile, json_encode($permissions, JSON_PRETTY_PRINT)) !== false;
    }

    private function defaults(): array
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
}
