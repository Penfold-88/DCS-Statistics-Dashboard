<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;
use DcsStats\Services\Cms\CmsPageStore;
use DcsStats\Services\Cms\CmsHtmlSanitizer;

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
        $contentHtml = '';
        if ($page !== null) {
            $content = (string)($page['content'] ?? '');
            $contentHtml = ($page['content_format'] ?? 'plain_text') === 'rich_html'
                ? (new CmsHtmlSanitizer())->sanitize($content)
                : nl2br(\e($content));
        }
        View::render('Public/cms_page.php', ['page' => $page, 'contentHtml' => $contentHtml]);
    }
}
