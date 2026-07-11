<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\CmsGalleriesPageService;

final class CmsGalleriesController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language', 'siteFeatures']);
        $currentAdmin = $this->requirePermission('manage_pages');
        if (!\isFeatureEnabled('cms_enabled')) {
            header('Location: cms_settings.php');
            exit;
        }
        $this->render('cms_galleries.php', (new CmsGalleriesPageService())->state($currentAdmin));
    }
}
