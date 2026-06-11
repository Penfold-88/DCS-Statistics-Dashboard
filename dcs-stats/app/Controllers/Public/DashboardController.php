<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;
use DcsStats\Services\DashboardPageService;

final class DashboardController
{
    public function index(): void
    {
        require_once DCS_ROOT_PATH . '/site_features.php';
        require_once DCS_ROOT_PATH . '/chart_theme.php';
        require_once DCS_ROOT_PATH . '/language.php';
        require_once DCS_ROOT_PATH . '/install_checkin.php';
        require_once DCS_ROOT_PATH . '/site-config/update_channel.php';
        require_once DCS_ROOT_PATH . '/site-config/version_tracker.php';

        if (!Installation::isConfigured()) {
            Installation::redirectToInstaller();
        }

        runInstallCheckinIfDue(getCurrentVersionInfo(), getUpdateChannelConfig());

        View::render('Public/dashboard.php', (new DashboardPageService())->getHomePageState());
    }
}
