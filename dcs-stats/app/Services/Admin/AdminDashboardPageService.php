<?php

namespace DcsStats\Services\Admin;

final class AdminDashboardPageService
{
    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::updateChannel();
        \DcsStats\Core\SupportBootstrap::versionTracker();
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::installCheckin();
        \DcsStats\Core\SupportBootstrap::apiConfig();
        \DcsStats\Core\SupportBootstrap::language();

        [$installDeleteMessage, $installDeleteMessageType] = $this->handleInstallerDelete();
        $stats = \getDashboardStats();
        $features = \loadSiteFeatures();
        $featureGroups = \getFeatureGroups();
        $maintenanceConfig = \loadMaintenanceConfig();
        $updateChannel = \getUpdateChannelConfig();
        $versionInfo = \initializeVersionTracking();
        \runInstallCheckinIfDue($versionInfo, $updateChannel);
        $apiConfigResult = \loadApiConfigWithFix();
        $apiConfig = $apiConfigResult['config'] ?? [];
        $siteConfig = $this->siteConfig();
        [$featureCount, $enabledFeatureCount] = $this->featureCounts($featureGroups, $features);

        return [
            'apiConfig' => $apiConfig,
            'apiEnabled' => !empty($apiConfig['use_api']),
            'apiHost' => $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiConfig['api_base_url'] ?? ''),
            'dataFiles' => $this->dataFiles(),
            'enabledEndpoints' => isset($apiConfig['enabled_endpoints']) && is_array($apiConfig['enabled_endpoints']) ? count($apiConfig['enabled_endpoints']) : 0,
            'enabledFeatureCount' => $enabledFeatureCount,
            'featureCount' => $featureCount,
            'featureGroups' => $featureGroups,
            'features' => $features,
            'installDeleteMessage' => $installDeleteMessage,
            'installDeleteMessageType' => $installDeleteMessageType,
            'installedBuild' => \getInstalledBuildLabel($versionInfo),
            'installedCommit' => !empty($versionInfo['commit_sha']) ? substr($versionInfo['commit_sha'], 0, 12) : 'Unknown',
            'installFilePresent' => file_exists(DCS_ROOT_PATH . '/site-config/install.php'),
            'maintenanceConfig' => $maintenanceConfig,
            'pageTitle' => \dcs_t('admin.dashboard.title'),
            'siteName' => $siteConfig['site_name'] ?? 'DCS Statistics',
            'stats' => $stats,
            'updateChannel' => $updateChannel,
            'versionInfo' => $versionInfo,
        ];
    }

    private function handleInstallerDelete(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'delete_installer') {
            return ['', ''];
        }

        \requirePermission('change_settings');
        \requireCSRFToken();

        $installPath = DCS_ROOT_PATH . '/site-config/install.php';
        $installRealPath = realpath($installPath);
        $adminRealPath = realpath(DCS_ROOT_PATH . '/site-config');

        if (!file_exists($installPath)) {
            return [\dcs_t('admin.dashboard.install_file_delete_missing'), 'success'];
        }

        if (
            $installRealPath === false ||
            $adminRealPath === false ||
            $installRealPath !== $adminRealPath . DIRECTORY_SEPARATOR . 'install.php' ||
            !is_file($installRealPath)
        ) {
            return [\dcs_t('admin.dashboard.install_file_delete_failed'), 'error'];
        }

        if (@unlink($installRealPath)) {
            \logAdminAction('INSTALLER_FILE_DELETE', ['file' => 'site-config/install.php']);
            return [\dcs_t('admin.dashboard.install_file_delete_success'), 'success'];
        }

        return [\dcs_t('admin.dashboard.install_file_delete_failed'), 'error'];
    }

    private function siteConfig(): array
    {
        $siteConfigFile = DCS_ROOT_PATH . '/site_config.json';
        return file_exists($siteConfigFile) ? (json_decode((string)file_get_contents($siteConfigFile), true) ?: []) : [];
    }

    private function featureCounts(array $featureGroups, array $features): array
    {
        $featureCount = 0;
        $enabledFeatureCount = 0;

        foreach ($featureGroups as $groupFeatures) {
            foreach ($groupFeatures as $featureKey => $featureLabel) {
                $featureCount++;
                if (!empty($features[$featureKey])) {
                    $enabledFeatureCount++;
                }
            }
        }

        return [$featureCount, $enabledFeatureCount];
    }

    private function dataFiles(): array
    {
        $backupDataDir = DCS_ROOT_PATH . '/site-config/data';
        return is_dir($backupDataDir) ? glob($backupDataDir . '/*.json') : [];
    }
}
