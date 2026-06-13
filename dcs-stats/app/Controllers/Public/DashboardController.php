<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;
use DcsStats\Services\DashboardPageService;

final class DashboardController
{
    public function index(): void
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::chartTheme();
        \DcsStats\Core\SupportBootstrap::language();
        \DcsStats\Core\SupportBootstrap::installCheckin();
        \DcsStats\Core\SupportBootstrap::updateChannel();
        \DcsStats\Core\SupportBootstrap::versionTracker();

        if (!Installation::isConfigured()) {
            Installation::redirectToInstaller();
        }

        runInstallCheckinIfDue(getCurrentVersionInfo(), getUpdateChannelConfig());

        View::render('Public/dashboard.php', (new DashboardPageService())->getHomePageState());
    }
}
