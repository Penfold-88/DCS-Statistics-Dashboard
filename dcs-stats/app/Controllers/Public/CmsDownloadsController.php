<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;
use DcsStats\Services\Cms\CmsDownloadStore;

final class CmsDownloadsController
{
    public function show(): void
    {
        \DcsStats\Core\SupportBootstrap::load('siteFeatures', 'language');
        if (!Installation::isConfigured()) {
            Installation::redirectToInstaller();
        }

        $enabled = \isFeatureEnabled('cms_enabled') && \isFeatureEnabled('cms_downloads_enabled');
        if (!$enabled) {
            http_response_code(404);
        }

        View::render('Public/cms_downloads.php', [
            'pageTitle' => \dcs_t('cms.downloads.title'),
            'downloads' => $enabled ? (new CmsDownloadStore())->enabled() : [],
            'enabled' => $enabled,
        ]);
    }
}
