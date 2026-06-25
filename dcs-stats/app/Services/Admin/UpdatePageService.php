<?php

namespace DcsStats\Services\Admin;

final class UpdatePageService
{
    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::updateChannel();
        \DcsStats\Core\SupportBootstrap::versionTracker();
        \DcsStats\Core\SupportBootstrap::devMode();
        \DcsStats\Core\SupportBootstrap::language();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $updateChannel = \getUpdateChannelConfig();
        $versionInfo = \initializeVersionTracking();
        $dashboardVersion = defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'Unknown';
        $installedBuild = \getInstalledBuildLabel($versionInfo);
        $installedCommit = !empty($versionInfo['commit_sha']) ? substr($versionInfo['commit_sha'], 0, 12) : 'Unknown';
        $installedDate = !empty($versionInfo['commit_date']) ? date('Y-m-d H:i:s', strtotime($versionInfo['commit_date'])) : 'Unknown';
        $lastUpdated = $versionInfo['updated_at'] ?? 'Unknown';
        $currentBranch = $versionInfo['branch'];

        return [
            'currentBranch' => $currentBranch,
            'dashboardVersion' => $dashboardVersion,
            'demoRestricted' => $demoRestricted,
            'installedBuild' => $installedBuild,
            'installedCommit' => $installedCommit,
            'installedDate' => $installedDate,
            'isDev' => \isDevMode(),
            'lastUpdated' => $lastUpdated,
            'pageTitle' => \dcs_t('admin.update.title'),
            'supportInfo' => [
                \dcs_t('admin.update.dashboard_version') => $dashboardVersion,
                \dcs_t('admin.update.installed_build') => $installedBuild,
                \dcs_t('admin.update.current_branch') => $currentBranch,
                \dcs_t('admin.update.update_channel') => $updateChannel['channel'],
                \dcs_t('admin.update.github_branch') => $updateChannel['branch'],
                \dcs_t('admin.update.installed_commit') => $installedCommit,
                \dcs_t('admin.update.installed_date') => $installedDate,
                \dcs_t('admin.dashboard.php_version') => PHP_VERSION,
                \dcs_t('admin.update.last_updated') => $lastUpdated,
            ],
            'updateChannel' => $updateChannel,
            'versionInfo' => $versionInfo,
        ];
    }
}
