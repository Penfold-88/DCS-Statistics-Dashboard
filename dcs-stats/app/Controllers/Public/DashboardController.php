<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;
use DcsStats\Services\DashboardPageService;
use DcsStats\Services\Cms\CmsLandingPageService;
use DcsStats\Services\Cms\CmsPageViewService;

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

        if (($_GET['view'] ?? '') !== 'statistics') {
            $landingPage = (new CmsLandingPageService())->selectedPage();
            if ($landingPage !== null) {
                View::render('Public/cms_page.php', (new CmsPageViewService())->state($landingPage));
                return;
            }
        }

        View::render('Public/dashboard.php', (new DashboardPageService())->getHomePageState());
    }
}
