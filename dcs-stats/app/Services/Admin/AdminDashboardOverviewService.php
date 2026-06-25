<?php

namespace DcsStats\Services\Admin;

final class AdminDashboardOverviewService
{
    public function build(array $apiConfig, array $featureGroups, array $features, array $versionInfo): array
    {
        [$featureCount, $enabledFeatureCount] = $this->featureCounts($featureGroups, $features);
        $siteConfig = $this->siteConfig();

        return [
            'apiEnabled' => !empty($apiConfig['use_api']),
            'apiHost' => $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiConfig['api_base_url'] ?? ''),
            'dataFiles' => $this->dataFiles(),
            'enabledEndpoints' => isset($apiConfig['enabled_endpoints']) && is_array($apiConfig['enabled_endpoints']) ? count($apiConfig['enabled_endpoints']) : 0,
            'enabledFeatureCount' => $enabledFeatureCount,
            'featureCount' => $featureCount,
            'installedBuild' => \getInstalledBuildLabel($versionInfo),
            'installedCommit' => !empty($versionInfo['commit_sha']) ? substr($versionInfo['commit_sha'], 0, 12) : 'Unknown',
            'installFilePresent' => file_exists(DCS_ROOT_PATH . '/site-config/install.php'),
            'siteName' => $siteConfig['site_name'] ?? 'DCS Statistics',
        ];
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
