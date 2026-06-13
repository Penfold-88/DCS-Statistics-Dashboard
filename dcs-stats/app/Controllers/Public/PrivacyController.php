<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;

final class PrivacyController
{
    public function index(): void
    {
        \DcsStats\Core\SupportBootstrap::siteMetadata();

        if (!Installation::isConfigured()) {
            Installation::redirectToInstaller();
        }

        View::render('Public/privacy.php', [
            'metadata' => loadSiteMetadata(),
        ]);
    }
}
