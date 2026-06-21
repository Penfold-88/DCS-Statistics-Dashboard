<?php

namespace DcsStats\Services\Admin;

use DcsStats\Services\Cms\CmsHtmlSanitizer;
use DcsStats\Services\Cms\CmsMediaService;
use DcsStats\Services\Cms\CmsPageStore;

final class CmsPageEditorService
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
        $id = preg_replace('/[^a-f0-9]/', '', (string)($_GET['edit'] ?? $_POST['page_id'] ?? ''));
        $editPage = $id !== '' ? $this->store->find($id) : null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            [$message, $messageType, $savedPage] = $this->save($currentAdmin, $editPage);
            if ($savedPage) {
                $editPage = $savedPage;
            }
        }
        return [
            'editPage' => $editPage,
            'mediaItems' => (new CmsMediaService())->items(),
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => $editPage ? \dcs_t('admin.cms.edit_page') : \dcs_t('admin.cms.create_page'),
        ];
    }

    private function save(array $currentAdmin, ?array $existing): array
    {
        if (!\verifyCSRFToken((string)($_POST['csrf_token'] ?? ''))) {
            return [\dcs_t('admin.cms.csrf_invalid'), 'error', null];
        }
        if (\isDemoRestricted($currentAdmin)) {
            return [\demoWriteLockMessage(), 'error', null];
        }
        $title = trim((string)($_POST['title'] ?? ''));
        $slug = trim((string)preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim((string)($_POST['slug'] ?? '')))), '-');
        $content = $this->sanitizer->sanitize((string)($_POST['content'] ?? ''));
        $plainContent = trim(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $seoTitle = trim((string)($_POST['seo_title'] ?? ''));
        $seoDescription = trim((string)($_POST['seo_description'] ?? ''));
        $seoImage = trim((string)($_POST['seo_image'] ?? ''));
        if ($title === '' || strlen($title) > 120 || $slug === '' || strlen($slug) > 80 || $plainContent === '' || strlen($content) > 200000 || strlen($seoTitle) > 70 || strlen($seoDescription) > 160) {
            return [\dcs_t('admin.cms.invalid_page'), 'error', null];
        }
        foreach ($this->store->all() as $page) {
            if (($page['slug'] ?? '') === $slug && ($page['id'] ?? '') !== ($existing['id'] ?? '')) {
                return [\dcs_t('admin.cms.slug_exists'), 'error', null];
            }
        }
        $allowedImages = array_column((new CmsMediaService())->items(), 'path');
        if ($seoImage !== '' && !in_array($seoImage, $allowedImages, true)) {
            $seoImage = '';
        }
        $now = gmdate('c');
        $page = [
            'id' => $existing['id'] ?? bin2hex(random_bytes(8)),
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'content_format' => 'rich_html',
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'seo_image' => $seoImage,
            'published' => isset($_POST['published']),
            'show_in_navigation' => isset($_POST['show_in_navigation']),
            'created_at' => $existing['created_at'] ?? $now,
            'updated_at' => $now,
        ];
        if (!$this->store->save($page)) {
            return [\dcs_t('admin.cms.save_failed'), 'error', null];
        }
        \logAdminActivity('CMS_PAGE_SAVE', $_SESSION['admin_id'], 'cms', $page['id'], ['slug' => $slug, 'published' => $page['published']]);
        return [\dcs_t('admin.cms.page_saved'), 'success', $page];
    }
}
