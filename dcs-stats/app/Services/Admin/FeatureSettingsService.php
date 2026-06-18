<?php

namespace DcsStats\Services\Admin;

final class FeatureSettingsService
{
    private FeatureSettingsMutationService $mutations;

    public function __construct(?FeatureSettingsMutationService $mutations = null)
    {
        $this->mutations = $mutations ?? new FeatureSettingsMutationService();
    }

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

        return $this->mutations->updateAll($input, $currentAdmin);
    }

    public function toggle(array $input, array $currentAdmin): array
    {
        $this->loadDependencies();

        return $this->mutations->toggle($input, $currentAdmin);
    }

    private function loadDependencies(): void
    {
        \DcsStats\Core\AdminBootstrap::auth();
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\AdminBootstrap::panel();
    }

}
