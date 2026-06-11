<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;

final class PrivacyController
{
    public function index(): void
    {
        require_once DCS_ROOT_PATH . '/site_metadata.php';

        if (!Installation::isConfigured()) {
            Installation::redirectToInstaller();
        }

        View::render('Public/privacy.php', [
            'metadata' => loadSiteMetadata(),
        ]);
    }
}
