<?php

namespace DcsStats\Services\Admin;

final class FeatureSettingsService
{
    public function getSettings(): array
    {
        $this->loadDependencies();

        return [
            'success' => true,
            'features' => loadSiteFeatures(),
            'groups' => getFeatureGroups(),
            'dependencies' => getFeatureDependencies(),
        ];
    }

    public function updateAll(array $input, array $currentAdmin): array
    {
        $this->loadDependencies();

        if (!isset($input['features']) || !is_array($input['features'])) {
            return ['status' => 400, 'payload' => ['error' => 'Invalid request data']];
        }

        $allFeatures = loadSiteFeatures();

        foreach (getFeatureGroups() as $features) {
            foreach ($features as $key => $label) {
                $allFeatures[$key] = isset($input['features'][$key]) && $input['features'][$key] === true;
            }
        }

        foreach ([
            'show_discord_link',
            'show_squadron_homepage',
            'discord_link_url',
            'squadron_homepage_url',
            'squadron_homepage_text',
        ] as $customKey) {
            if (isset($input['features'][$customKey])) {
                $allFeatures[$customKey] = is_bool($input['features'][$customKey])
                    ? (bool)$input['features'][$customKey]
                    : $input['features'][$customKey];
            }
        }

        $allFeatures = $this->applyDependencies($allFeatures);

        if (!saveSiteFeatures($allFeatures)) {
            return ['status' => 500, 'payload' => ['error' => 'Failed to save settings']];
        }

        logAdminActivity('SETTINGS_CHANGE', $currentAdmin['id'], 'settings', 'site_features', $allFeatures);

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'message' => 'Settings updated successfully',
                'features' => $allFeatures,
            ],
        ];
    }

    public function toggle(array $input, array $currentAdmin): array
    {
        $this->loadDependencies();

        if (!isset($input['feature']) || !isset($input['enabled'])) {
            return ['status' => 400, 'payload' => ['error' => 'Feature and enabled status required']];
        }

        $featureKey = (string)$input['feature'];
        if (!$this->isKnownGroupedFeature($featureKey)) {
            return ['status' => 400, 'payload' => ['error' => 'Invalid feature']];
        }

        $features = loadSiteFeatures();
        $features[$featureKey] = (bool)$input['enabled'];

        if (!$input['enabled']) {
            $dependencies = getFeatureDependencies();
            if (isset($dependencies[$featureKey])) {
                foreach ($dependencies[$featureKey] as $child) {
                    $features[$child] = false;
                }
            }
        }

        if (!saveSiteFeatures($features)) {
            return ['status' => 500, 'payload' => ['error' => 'Failed to update feature']];
        }

        logAdminActivity('SETTINGS_CHANGE', $currentAdmin['id'], 'settings', $featureKey, $input['enabled']);

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'message' => 'Feature toggled successfully',
                'feature' => $featureKey,
                'enabled' => $features[$featureKey],
                'features' => $features,
            ],
        ];
    }

    private function loadDependencies(): void
    {
        \DcsStats\Core\AdminBootstrap::auth();
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\AdminBootstrap::panel();
    }

    private function applyDependencies(array $features): array
    {
        foreach (getFeatureDependencies() as $parent => $children) {
            if (empty($features[$parent])) {
                foreach ($children as $child) {
                    $features[$child] = false;
                }
            }
        }

        return $features;
    }

    private function isKnownGroupedFeature(string $featureKey): bool
    {
        foreach (getFeatureGroups() as $groupFeatures) {
            if (isset($groupFeatures[$featureKey])) {
                return true;
            }
        }

        return false;
    }
}
