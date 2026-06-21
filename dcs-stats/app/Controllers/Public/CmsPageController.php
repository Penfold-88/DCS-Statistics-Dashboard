<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;
use DcsStats\Services\Cms\CmsPageStore;
use DcsStats\Services\Cms\CmsPageViewService;

final class CmsPageController
{
    public function show(): void
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        if (!Installation::isConfigured()) {
            Installation::redirectToInstaller();
        }

        $slug = strtolower(trim((string)($_GET['slug'] ?? '')));
        $slug = trim((string)preg_replace('/[^a-z0-9-]+/', '-', $slug), '-');
        $page = \isFeatureEnabled('cms_enabled') ? (new CmsPageStore())->findPublishedBySlug($slug) : null;
        if ($page === null) {
            http_response_code(404);
        }
        View::render('Public/cms_page.php', (new CmsPageViewService())->state($page));
    }
}
