<?php

namespace DcsStats\Services\Admin;

final class AdminDashboardPageService
{
    private AdminDashboardInstallerService $installerService;
    private AdminDashboardOverviewService $overviewService;

    public function __construct(
        ?AdminDashboardInstallerService $installerService = null,
        ?AdminDashboardOverviewService $overviewService = null
    ) {
        $this->installerService = $installerService ?? new AdminDashboardInstallerService();
        $this->overviewService = $overviewService ?? new AdminDashboardOverviewService();
    }

    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::updateChannel();
        \DcsStats\Core\SupportBootstrap::versionTracker();
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::installCheckin();
        \DcsStats\Core\SupportBootstrap::apiConfig();
        \DcsStats\Core\SupportBootstrap::language();

        [$installDeleteMessage, $installDeleteMessageType] = $this->installerService->handleRequest();
        $stats = \getDashboardStats();
        $features = \loadSiteFeatures();
        $featureGroups = \getFeatureGroups();
        $maintenanceConfig = \loadMaintenanceConfig();
        $updateChannel = \getUpdateChannelConfig();
        $versionInfo = \initializeVersionTracking();
        \runInstallCheckinIfDue($versionInfo, $updateChannel);
        $apiConfigResult = \loadApiConfigWithFix();
        $apiConfig = $apiConfigResult['config'] ?? [];
        $overview = $this->overviewService->build($apiConfig, $featureGroups, $features, $versionInfo);

        return array_merge($overview, [
            'apiConfig' => $apiConfig,
            'featureGroups' => $featureGroups,
            'features' => $features,
            'installDeleteMessage' => $installDeleteMessage,
            'installDeleteMessageType' => $installDeleteMessageType,
            'maintenanceConfig' => $maintenanceConfig,
            'pageTitle' => \dcs_t('admin.dashboard.title'),
            'stats' => $stats,
            'updateChannel' => $updateChannel,
            'versionInfo' => $versionInfo,
        ]);
    }
}
