<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;

final class LeaderboardController
{
    public function index(): void
    {
        require_once DCS_ROOT_PATH . '/site_features.php';
        require_once DCS_ROOT_PATH . '/table-responsive.php';
        require_once DCS_ROOT_PATH . '/chart_theme.php';
        require_once DCS_ROOT_PATH . '/language.php';

        if (!Installation::isConfigured()) {
            Installation::redirectToInstaller();
        }

        View::render('Public/leaderboard.php');
    }
}
