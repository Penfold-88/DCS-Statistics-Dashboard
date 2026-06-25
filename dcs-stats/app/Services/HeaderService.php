<?php

namespace DcsStats\Services;

final class HeaderService
{
    private PublicSecurityHeaderService $securityHeaderService;
    private PublicMaintenanceGate $maintenanceGate;
    private PublicHeaderBrandingService $brandingService;
    private HeaderPreviewService $previewService;
    private PublicDemoModeDetector $demoModeDetector;

    public function __construct(
        ?PublicSecurityHeaderService $securityHeaderService = null,
        ?PublicMaintenanceGate $maintenanceGate = null,
        ?PublicHeaderBrandingService $brandingService = null,
        ?HeaderPreviewService $previewService = null,
        ?PublicDemoModeDetector $demoModeDetector = null
    ) {
        $this->securityHeaderService = $securityHeaderService ?? new PublicSecurityHeaderService();
        $this->maintenanceGate = $maintenanceGate ?? new PublicMaintenanceGate();
        $this->brandingService = $brandingService ?? new PublicHeaderBrandingService();
        $this->previewService = $previewService ?? new HeaderPreviewService();
        $this->demoModeDetector = $demoModeDetector ?? new PublicDemoModeDetector();
    }

    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::apiConfig();
        \DcsStats\Core\SupportBootstrap::siteMetadata();
        \DcsStats\Core\SupportBootstrap::language();
        \DcsStats\Core\AdminBootstrap::demo();

        $this->securityHeaderService->send();
        $this->maintenanceGate->exitIfNeeded();

        $branding = $this->brandingService->state();

        return [
            'siteName' => $branding['siteName'],
            'siteMetadata' => \loadSiteMetadata(),
            'headerBranding' => $branding['headerBranding'],
            'headerLogoPath' => $branding['headerLogoPath'],
            'showHeaderLogo' => $branding['showHeaderLogo'],
            'showHeaderText' => $branding['showHeaderText'],
            'hasPageBackgroundImage' => $branding['hasPageBackgroundImage'],
            'previewColors' => $this->previewService->colors($_GET),
            'frontendDemoMode' => $this->demoModeDetector->isEnabled(),
        ];
    }
}
