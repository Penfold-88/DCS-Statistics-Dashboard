<?php

namespace DcsStats\Services\Admin;

use DcsStats\Services\Cms\CmsDownloadStore;

final class CmsDownloadsPageService
{
    private CmsDownloadStore $store;

    public function __construct(?CmsDownloadStore $store = null)
    {
        $this->store = $store ?? new CmsDownloadStore();
    }

    public function state(array $currentAdmin): array
    {
        $message = '';
        $messageType = '';
        $editId = preg_replace('/[^a-f0-9]/', '', (string)($_GET['edit'] ?? $_POST['download_id'] ?? ''));

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            [$message, $messageType, $editId] = $this->handle($currentAdmin, $editId);
        }

        return [
            'currentAdmin' => $currentAdmin,
            'pageTitle' => \dcs_t('admin.cms.downloads'),
            'downloadsEnabled' => \isFeatureEnabled('cms_downloads_enabled'),
            'downloads' => $this->store->all(),
            'editDownload' => $editId !== '' ? $this->store->find($editId) : null,
            'message' => $message,
            'messageType' => $messageType,
        ];
    }

    private function handle(array $currentAdmin, string $editId): array
    {
        if (!\verifyCSRFToken((string)($_POST['csrf_token'] ?? ''))) return [\dcs_t('admin.cms.csrf_invalid'), 'error', $editId];
        if (\isDemoRestricted($currentAdmin)) return [\demoWriteLockMessage(), 'error', $editId];

        $action = (string)($_POST['action'] ?? '');

        if ($action === 'save_settings') {
            $features = \loadSiteFeatures();
            $features['cms_downloads_enabled'] = isset($_POST['cms_downloads_enabled']);
            if (!\saveSiteFeatures($features)) return [\dcs_t('admin.cms.save_failed'), 'error', $editId];
            \logAdminActivity('CMS_DOWNLOAD_SETTINGS', $_SESSION['admin_id'], 'cms', 'downloads', ['enabled' => $features['cms_downloads_enabled']]);
            return [\dcs_t('admin.cms.download_settings_saved'), 'success', $editId];
        }

        if ($action === 'delete_download') {
            if ($editId === '' || !$this->store->find($editId)) return [\dcs_t('admin.cms.download_not_found'), 'error', ''];
            if (!$this->store->delete($editId)) return [\dcs_t('admin.cms.download_delete_failed'), 'error', $editId];
            \logAdminActivity('CMS_DOWNLOAD_DELETE', $_SESSION['admin_id'], 'cms', $editId, []);
            return [\dcs_t('admin.cms.download_deleted'), 'success', ''];
        }

        if ($action === 'save_download') {
            $existing = $editId !== '' ? $this->store->find($editId) : null;
            $download = $this->downloadFromRow($_POST, $existing ?: null);
            if ($download === null) return [\dcs_t('admin.cms.download_invalid'), 'error', $editId];
            if (!$this->store->save($download)) return [\dcs_t('admin.cms.save_failed'), 'error', $editId];
            \logAdminActivity('CMS_DOWNLOAD_SAVE', $_SESSION['admin_id'], 'cms', $download['id'], ['enabled' => $download['enabled']]);
            return [\dcs_t('admin.cms.download_saved'), 'success', $download['id']];
        }

        if ($action === 'add_downloads') {
            $rows = is_array($_POST['downloads'] ?? null) ? $_POST['downloads'] : [];
            $downloads = [];
            foreach ($rows as $row) {
                if (!is_array($row)) continue;
                $download = $this->downloadFromRow($row, null);
                if ($download !== null) $downloads[] = $download;
            }
            if (!$downloads) return [\dcs_t('admin.cms.downloads_required'), 'error', $editId];
            if (!$this->store->saveMany($downloads)) return [\dcs_t('admin.cms.save_failed'), 'error', $editId];
            \logAdminActivity('CMS_DOWNLOAD_BATCH_SAVE', $_SESSION['admin_id'], 'cms', 'downloads', ['count' => count($downloads)]);
            return [\dcs_t('admin.cms.downloads_saved', ['count' => count($downloads)]), 'success', ''];
        }

        return [\dcs_t('admin.cms.invalid_action'), 'error', $editId];
    }

    private function downloadFromRow(array $row, ?array $existing): ?array
    {
        $title = substr(trim((string)($row['title'] ?? '')), 0, 160);
        $url = trim((string)($row['url'] ?? ''));
        if ($title === '' && $url === '') return null;
        if ($title === '' || !$this->isSafeUrl($url)) return null;

        $now = gmdate('c');

        return [
            'id' => $existing['id'] ?? bin2hex(random_bytes(8)),
            'title' => $title,
            'url' => $url,
            'description' => substr(trim((string)($row['description'] ?? '')), 0, 700),
            'category' => substr(trim((string)($row['category'] ?? '')), 0, 80),
            'version' => substr(trim((string)($row['version'] ?? '')), 0, 40),
            'file_size' => substr(trim((string)($row['file_size'] ?? '')), 0, 40),
            'button_label' => substr(trim((string)($row['button_label'] ?? '')), 0, 40),
            'sort_order' => max(0, min(9999, (int)($row['sort_order'] ?? 100))),
            'featured' => isset($row['featured']),
            'enabled' => isset($row['enabled']),
            'created_at' => $existing['created_at'] ?? $now,
            'updated_at' => $now,
        ];
    }

    private function isSafeUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true);
    }
}
