<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\CmsSettingsPageService;

final class CmsSettingsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language', 'siteFeatures']);
        $currentAdmin = $this->requirePermission('manage_pages');
        $this->render('cms_settings.php', (new CmsSettingsPageService())->state($currentAdmin));
    }
}
