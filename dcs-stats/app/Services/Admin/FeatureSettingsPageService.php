<?php

namespace DcsStats\Services\Admin;

final class FeatureSettingsPageService
{
    private array $lockedFeatures = [];

    public function state(array $currentAdmin): array
    {
        require_once DCS_ROOT_PATH . '/language.php';
        require_once DCS_ROOT_PATH . '/site_features.php';
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

        $this->lockedFeatures = [
            'leaderboard_sorties' => \dcs_t('admin.settings.locked_sorties_reason'),
        ];
        $demoRestricted = \isDemoRestricted($currentAdmin);
        $dynamicServerFeatures = $this->detectedServerCardFeatures();
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$message, $messageType] = $this->handlePost($dynamicServerFeatures, $demoRestricted);
        }

        $featureGroups = \getFeatureGroups();
        if (!empty($dynamicServerFeatures)) {
            $featureGroups['Server Features'] = array_merge(
                $featureGroups['Server Features'],
                $dynamicServerFeatures
            );
        }

        $featureGroupLabels = [];
        $featureLabels = [];
        foreach ($featureGroups as $groupName => $features) {
            $featureGroupLabels[$groupName] = $this->groupLabel($groupName);
            foreach ($features as $key => $label) {
                $featureLabels[$key] = $this->featureLabel($key, $label);
            }
        }

        return [
            'currentFeatures' => \loadSiteFeatures(),
            'demoRestricted' => $demoRestricted,
            'dependencies' => \getFeatureDependencies(),
            'dynamicServerFeatures' => $dynamicServerFeatures,
            'featureGroups' => $featureGroups,
            'featureGroupLabels' => $featureGroupLabels,
            'featureLabels' => $featureLabels,
            'lockedFeatures' => $this->lockedFeatures,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.settings.title'),
        ];
    }

    public function groupLabel($groupName): string
    {
        $key = 'admin.settings.group.' . $this->translationKey($groupName);
        $translated = \dcs_t($key);
        return $translated === $key ? $groupName : $translated;
    }

    public function featureLabel($featureKey, $fallback): string
    {
        $key = 'admin.settings.feature.' . $featureKey;
        $translated = \dcs_t($key);
        return $translated === $key ? $fallback : $translated;
    }

    private function translationKey($value): string
    {
        return preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)$value)));
    }

    private function detectedServerCardFeatures(): array
    {
        try {
            $client = \createEnhancedAPIClient();
            $response = $client->request('/servers', null, 'GET');
            $servers = is_array($response) ? $response : [];
            $features = [];

            foreach ($servers as $index => $server) {
                if (!is_array($server)) {
                    continue;
                }

                $name = trim((string)($server['name'] ?? \dcs_t('admin.settings.server_number', ['number' => $index + 1])));
                if ($name === '') {
                    $name = \dcs_t('admin.settings.server_number', ['number' => $index + 1]);
                }

                $features[\serverCardFeatureKey($name)] = $name;
            }

            return $features;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function handlePost(array $dynamicServerFeatures, bool $demoRestricted): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [\dcs_t('admin.settings.csrf_invalid'), 'error'];
        }

        if ($demoRestricted) {
            return [\demoWriteLockMessage(), 'error'];
        }

        $allFeatures = \loadSiteFeatures();
        $featureGroupsForSave = \getFeatureGroups();
        if (!empty($dynamicServerFeatures)) {
            $featureGroupsForSave['Server Features'] = array_merge(
                $featureGroupsForSave['Server Features'],
                $dynamicServerFeatures
            );
        }

        foreach ($featureGroupsForSave as $group => $features) {
            foreach ($features as $key => $label) {
                $allFeatures[$key] = isset($_POST['features'][$key]);
            }
        }

        foreach ($this->lockedFeatures as $lockedKey => $reason) {
            $allFeatures[$lockedKey] = false;
        }

        foreach (\getFeatureDependencies() as $parent => $children) {
            if (!$allFeatures[$parent]) {
                foreach ($children as $child) {
                    $allFeatures[$child] = false;
                }
            }
        }

        if (!\saveSiteFeatures($allFeatures)) {
            return [\dcs_t('admin.settings.save_failed'), 'error'];
        }

        \logAdminActivity('SETTINGS_CHANGE', $_SESSION['admin_id'], 'settings', 'site_features', $allFeatures);

        return [\dcs_t('admin.settings.save_success'), 'success'];
    }
}
