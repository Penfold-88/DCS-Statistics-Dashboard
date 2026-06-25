<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Core\View;
use DcsStats\Services\Cms\CmsPageStore;
use DcsStats\Services\Cms\CmsPageViewService;

final class CmsPagePreviewController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language', 'siteFeatures']);
        $this->requirePermission('manage_pages');
        $id = preg_replace('/[^a-f0-9]/', '', (string)($_GET['id'] ?? ''));
        $page = $id !== '' ? (new CmsPageStore())->find($id) : null;
        if (!$page) {
            http_response_code(404);
        }
        $state = (new CmsPageViewService())->state($page);
        $state['isPreview'] = true;
        View::render('Public/cms_page.php', $state);
    }
}
