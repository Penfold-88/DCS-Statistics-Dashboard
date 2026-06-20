<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\CmsPagesPageService;

final class CmsPagesController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language', 'siteFeatures']);
        $currentAdmin = $this->requirePermission('manage_pages');
        if (!\isFeatureEnabled('cms_enabled')) {
            header('Location: cms_settings.php');
            exit;
        }
        $this->render('cms_pages.php', (new CmsPagesPageService())->state($currentAdmin));
    }
}
