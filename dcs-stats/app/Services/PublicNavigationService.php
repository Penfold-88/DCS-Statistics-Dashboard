<?php

namespace DcsStats\Services;

final class PublicNavigationService
{
    private PublicNavigationFeatureService $featureService;
    private PublicMenuService $menuService;
    private PublicNavigationPolicy $policy;

    public function __construct(
        ?PublicNavigationFeatureService $featureService = null,
        ?PublicMenuService $menuService = null,
        ?PublicNavigationPolicy $policy = null
    ) {
        $this->featureService = $featureService ?? new PublicNavigationFeatureService();
        $this->menuService = $menuService ?? new PublicMenuService();
        $this->policy = $policy ?? new PublicNavigationPolicy();
    }

    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::language();

        return [
            'menuItems' => $this->menuService->items(),
            'customLinks' => $this->featureService->customLinks(),
            'customLinksMenuText' => $this->featureService->customLinksMenuText(),
            'serverScopeEnabled' => \isFeatureEnabled('server_scope_filter'),
            'serverCardVisibility' => $this->featureService->serverCardVisibility(),
        ];
    }

    public function menuConfigPath(): string
    {
        return $this->menuService->configPath();
    }

    public function label(array $item): string
    {
        return $this->policy->label($item);
    }

    public function shouldShow(array $item): bool
    {
        return $this->policy->shouldShow($item);
    }
}
