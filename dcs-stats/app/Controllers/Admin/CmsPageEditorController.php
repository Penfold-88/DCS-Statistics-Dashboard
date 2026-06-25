<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\CmsPageEditorService;

final class CmsPageEditorController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language', 'siteFeatures']);
        $currentAdmin = $this->requirePermission('manage_pages');
        if (!\isFeatureEnabled('cms_enabled')) {
            header('Location: cms_settings.php');
            exit;
        }
        $this->render('cms_page_edit.php', (new CmsPageEditorService())->state($currentAdmin));
    }
}
