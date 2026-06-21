<?php

namespace DcsStats\Services\Admin;

use DcsStats\Services\Cms\CmsPageStore;
use DcsStats\Services\Cms\CmsHtmlSanitizer;
use DcsStats\Services\Cms\CmsMediaService;

final class CmsPagesPageService
{
    private CmsPageStore $store;
    private CmsHtmlSanitizer $sanitizer;

    public function __construct(?CmsPageStore $store = null, ?CmsHtmlSanitizer $sanitizer = null)
    {
        $this->store = $store ?? new CmsPageStore();
        $this->sanitizer = $sanitizer ?? new CmsHtmlSanitizer();
    }

    public function state(array $currentAdmin): array
    {
        $message = '';
        $messageType = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            [$message, $messageType] = $this->handlePost($currentAdmin);
        }

        $editPage = !empty($_GET['edit']) ? $this->store->find((string)$_GET['edit']) : null;

        return [
            'editPage' => $editPage,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.cms.pages_title'),
            'pages' => $this->store->all(),
            'mediaItems' => (new CmsMediaService())->items(),
        ];
    }

    private function handlePost(array $currentAdmin): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [\dcs_t('admin.cms.csrf_invalid'), 'error'];
        }
        if (\isDemoRestricted($currentAdmin)) {
            return [\demoWriteLockMessage(), 'error'];
        }

        $action = (string)($_POST['action'] ?? '');
        if ($action === 'delete_page') {
            $id = preg_replace('/[^a-f0-9]/', '', (string)($_POST['page_id'] ?? ''));
            if ($id === '' || $this->store->find($id) === null || !$this->store->delete($id)) {
                return [\dcs_t('admin.cms.delete_failed'), 'error'];
            }
            $features = \loadSiteFeatures();
            if (($features['cms_homepage_page_id'] ?? '') === $id) {
                $features['cms_homepage_page_id'] = '';
                \saveSiteFeatures($features);
            }
            \logAdminActivity('CMS_PAGE_DELETE', $_SESSION['admin_id'], 'cms', $id, []);
            return [\dcs_t('admin.cms.page_deleted'), 'success'];
        }

        if ($action !== 'save_page') {
            return [\dcs_t('admin.cms.invalid_action'), 'error'];
        }

        $id = preg_replace('/[^a-f0-9]/', '', (string)($_POST['page_id'] ?? ''));
        $existing = $id !== '' ? $this->store->find($id) : null;
        $title = trim((string)($_POST['title'] ?? ''));
        $slug = strtolower(trim((string)($_POST['slug'] ?? '')));
        $slug = trim((string)preg_replace('/[^a-z0-9-]+/', '-', $slug), '-');
        $content = $this->sanitizer->sanitize((string)($_POST['content'] ?? ''));
        $plainContent = trim(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($title === '' || strlen($title) > 240 || $slug === '' || strlen($slug) > 80 || $plainContent === '' || strlen($content) > 200000) {
            return [\dcs_t('admin.cms.invalid_page'), 'error'];
        }
        foreach ($this->store->all() as $page) {
            if (($page['slug'] ?? '') === $slug && ($page['id'] ?? '') !== $id) {
                return [\dcs_t('admin.cms.slug_exists'), 'error'];
            }
        }

        $now = gmdate('c');
        $page = [
            'id' => $existing['id'] ?? bin2hex(random_bytes(8)),
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'content_format' => 'rich_html',
            'published' => isset($_POST['published']),
            'show_in_navigation' => isset($_POST['show_in_navigation']),
            'created_at' => $existing['created_at'] ?? $now,
            'updated_at' => $now,
        ];
        if (!$this->store->save($page)) {
            return [\dcs_t('admin.cms.save_failed'), 'error'];
        }
        \logAdminActivity('CMS_PAGE_SAVE', $_SESSION['admin_id'], 'cms', $page['id'], ['slug' => $slug, 'published' => $page['published']]);
        return [\dcs_t('admin.cms.page_saved'), 'success'];
    }
}
