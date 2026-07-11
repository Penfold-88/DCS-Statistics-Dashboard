<?php

namespace DcsStats\Services\Admin;

final class FeatureSettingsPageService
{
    private FeatureSettingsFormActionService $actions;
    private FeatureSettingsServerDetector $serverDetector;
    private array $lockedFeatures = [];

    public function __construct(?FeatureSettingsServerDetector $serverDetector = null, ?FeatureSettingsFormActionService $actions = null)
    {
        $this->serverDetector = $serverDetector ?? new FeatureSettingsServerDetector();
        $this->actions = $actions ?? new FeatureSettingsFormActionService();
    }

    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::language();
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::apiClient();

        $this->lockedFeatures = [
            'leaderboard_sorties' => \dcs_t('admin.settings.locked_sorties_reason'),
        ];
        $demoRestricted = \isDemoRestricted($currentAdmin);
        $dynamicServerFeatures = $this->serverDetector->detectedServerCardFeatures();
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$message, $messageType] = $this->actions->handle($dynamicServerFeatures, $this->lockedFeatures, $demoRestricted);
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
}
