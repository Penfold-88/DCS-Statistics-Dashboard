<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;

final class PilotCreditsController
{
    public function index(): void
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::tableResponsive();
        \DcsStats\Core\SupportBootstrap::language();

        if (!Installation::isConfigured()) {
            Installation::redirectToInstaller();
        }

        View::render('Public/pilot_credits.php');
    }
}
