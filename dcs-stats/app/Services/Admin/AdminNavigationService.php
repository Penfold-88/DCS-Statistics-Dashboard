<?php

namespace DcsStats\Services\Admin;

final class AdminNavigationService
{
    private const STATISTICS_PAGES = [
        'settings.php',
        'api_settings.php',
        'api_health.php',
    ];

    private const WEBSITE_PAGES = [
        'menu_manager.php',
        'custom_links.php',
        'discord_settings.php',
        'squadron_settings.php',
        'metadata.php',
        'themes.php',
    ];

    private const GLOBAL_PAGES = [
        'admins.php',
        'permissions.php',
        'language_settings.php',
        'settings_backup.php',
        'maintenance.php',
        'update.php',
    ];

    private const CMS_PAGES = [
        'cms_settings.php',
        'cms_pages.php',
        'cms_page_edit.php',
        'cms_page_preview.php',
    ];

    private const NAV_PERMISSIONS = [
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
        'manage_pages',
    ];

    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
        $permissions = $this->permissions();

        return [
            'canShowStatistics' => $permissions['manage_api'] || $permissions['manage_features'],
            'canShowWebsite' => $permissions['manage_features'] || $permissions['manage_discord'] || $permissions['manage_squadrons'] || $permissions['manage_themes'],
            'canShowCms' => $permissions['manage_pages'],
            'cmsEnabled' => \isFeatureEnabled('cms_enabled'),
            'canShowGlobal' => in_array(true, array_intersect_key($permissions, array_flip([
                'manage_admins',
                'manage_permissions',
                'manage_features',
                'manage_maintenance',
                'manage_updates',
            ])), true),
            'currentPage' => $currentPage,
            'demoMode' => function_exists('isDemoMode') && \isDemoMode(),
            'demoRestricted' => function_exists('isDemoRestricted') && \isDemoRestricted($currentAdmin),
            'isStatisticsPage' => in_array($currentPage, self::STATISTICS_PAGES, true),
            'isWebsitePage' => in_array($currentPage, self::WEBSITE_PAGES, true),
            'isCmsPage' => in_array($currentPage, self::CMS_PAGES, true),
            'isGlobalPage' => in_array($currentPage, self::GLOBAL_PAGES, true),
            'permissions' => $permissions,
        ];
    }

    private function permissions(): array
    {
        $permissionKeys = array_unique(array_merge([
            'view_logs',
            'export_data',
        ], self::NAV_PERMISSIONS));
        $permissions = [];

        foreach ($permissionKeys as $permission) {
            $permissions[$permission] = \hasPermission($permission);
        }

        return $permissions;
    }
}
