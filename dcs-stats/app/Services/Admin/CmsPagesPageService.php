<?php

namespace DcsStats\Services\Admin;

use DcsStats\Services\Cms\CmsPageStore;

final class CmsPagesPageService
{
    private CmsPageStore $store;

    public function __construct(?CmsPageStore $store = null)
    {
        $this->store = $store ?? new CmsPageStore();
    }

    public function state(array $currentAdmin): array
    {
        $message = '';
        $messageType = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            [$message, $messageType] = $this->handlePost($currentAdmin);
        }

        return [
            'landingPageId' => (string)\getFeatureValue('cms_homepage_page_id', ''),
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.cms.pages_title'),
            'pages' => $this->store->all(),
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

        if ($action !== 'duplicate_page') {
            return [\dcs_t('admin.cms.invalid_action'), 'error'];
        }
        $id = preg_replace('/[^a-f0-9]/', '', (string)($_POST['page_id'] ?? ''));
        $source = $id !== '' ? $this->store->find($id) : null;
        if (!$source) {
            return [\dcs_t('admin.cms.duplicate_failed'), 'error'];
        }
        $now = gmdate('c');
        $copy = $source;
        $copy['id'] = bin2hex(random_bytes(8));
        $suffix = ' ' . \dcs_t('admin.cms.copy_suffix');
        $copy['title'] = substr(trim((string)$source['title']), 0, 120 - strlen($suffix)) . $suffix;
        $copy['slug'] = $this->uniqueCopySlug((string)$source['slug']);
        $copy['published'] = false;
        $copy['show_in_navigation'] = false;
        $copy['created_at'] = $now;
        $copy['updated_at'] = $now;
        if (!$this->store->save($copy)) {
            return [\dcs_t('admin.cms.duplicate_failed'), 'error'];
        }
        \logAdminActivity('CMS_PAGE_DUPLICATE', $_SESSION['admin_id'], 'cms', $copy['id'], ['source_id' => $id]);
        return [\dcs_t('admin.cms.page_duplicated'), 'success'];
    }

    private function uniqueCopySlug(string $slug): string
    {
        $used = array_column($this->store->all(), 'slug');
        $base = substr(trim($slug, '-') . '-copy', 0, 75);
        $candidate = $base;
        $counter = 2;
        while (in_array($candidate, $used, true)) {
            $candidate = substr($base, 0, 74) . '-' . $counter++;
        }
        return $candidate;
    }
}
