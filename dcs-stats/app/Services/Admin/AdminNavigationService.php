<?php

namespace DcsStats\Services\Admin;

final class AdminNavigationService
{
    private const SETTINGS_PAGES = [
        'settings.php',
        'metadata.php',
        'language_settings.php',
        'custom_links.php',
        'settings_backup.php',
        'api_settings.php',
        'api_health.php',
        'themes.php',
        'discord_settings.php',
        'squadron_settings.php',
        'admins.php',
        'permissions.php',
        'maintenance.php',
        'update.php',
    ];

    private const SETTINGS_PERMISSIONS = [
        'change_settings',
        'manage_admins',
        'manage_permissions',
        'manage_api',
        'manage_features',
        'manage_maintenance',
        'manage_updates',
        'manage_discord',
        'manage_squadrons',
        'manage_themes',
    ];

    public function state(array $currentAdmin): array
    {
        $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
        $permissions = $this->permissions();

        return [
            'canShowSettings' => in_array(true, array_intersect_key($permissions, array_flip(self::SETTINGS_PERMISSIONS)), true),
            'currentPage' => $currentPage,
            'demoMode' => function_exists('isDemoMode') && \isDemoMode(),
            'demoRestricted' => function_exists('isDemoRestricted') && \isDemoRestricted($currentAdmin),
            'isSettingsPage' => in_array($currentPage, self::SETTINGS_PAGES, true),
            'permissions' => $permissions,
            'settingsPages' => self::SETTINGS_PAGES,
        ];
    }

    private function permissions(): array
    {
        $permissionKeys = array_unique(array_merge([
            'view_logs',
            'export_data',
        ], self::SETTINGS_PERMISSIONS));
        $permissions = [];

        foreach ($permissionKeys as $permission) {
            $permissions[$permission] = \hasPermission($permission);
        }

        return $permissions;
    }
}
