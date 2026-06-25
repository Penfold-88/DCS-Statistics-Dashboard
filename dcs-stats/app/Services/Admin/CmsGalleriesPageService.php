<?php

namespace DcsStats\Services\Admin;

use DcsStats\Services\Cms\CmsGalleryStore;
use DcsStats\Services\Cms\CmsMediaService;
use DcsStats\Services\Cms\CmsPageStore;

final class CmsGalleriesPageService
{
    private CmsGalleryStore $store;
    private CmsMediaService $media;

    public function __construct(?CmsGalleryStore $store = null, ?CmsMediaService $media = null)
    {
        $this->store = $store ?? new CmsGalleryStore();
        $this->media = $media ?? new CmsMediaService();
    }

    public function state(array $currentAdmin): array
    {
        $message = '';
        $messageType = '';
        $editId = preg_replace('/[^a-f0-9]/', '', (string)($_GET['edit'] ?? $_POST['gallery_id'] ?? ''));
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            [$message, $messageType, $editId] = $this->handle($currentAdmin, $editId);
        }
        return [
            'currentAdmin' => $currentAdmin,
            'pageTitle' => \dcs_t('admin.cms.galleries'),
            'galleryEnabled' => \isFeatureEnabled('cms_gallery_enabled'),
            'galleries' => $this->store->all(),
            'editGallery' => $editId !== '' ? $this->store->find($editId) : null,
            'mediaItems' => $this->media->items(),
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
            $features['cms_gallery_enabled'] = isset($_POST['cms_gallery_enabled']);
            if (!\saveSiteFeatures($features)) return [\dcs_t('admin.cms.save_failed'), 'error', $editId];
            \logAdminActivity('CMS_GALLERY_SETTINGS', $_SESSION['admin_id'], 'cms', 'galleries', ['enabled' => $features['cms_gallery_enabled']]);
            return [\dcs_t('admin.cms.gallery_settings_saved'), 'success', $editId];
        }
        if ($action === 'upload_image') {
            $result = $this->media->uploadMany($_FILES['images'] ?? null);
            return [(string)($result['message'] ?? ($result['success'] ? \dcs_t('admin.cms.gallery_image_uploaded') : \dcs_t('admin.cms.media_upload_failed'))), !empty($result['success']) ? 'success' : 'error', $editId];
        }
        if ($action === 'delete_gallery') {
            if ($editId === '' || !$this->store->find($editId)) return [\dcs_t('admin.cms.gallery_not_found'), 'error', ''];
            foreach ((new CmsPageStore())->all() as $page) {
                if (strpos((string)($page['content'] ?? ''), 'data-gallery="' . $editId . '"') !== false) return [\dcs_t('admin.cms.gallery_in_use'), 'error', $editId];
            }
            if (!$this->store->delete($editId)) return [\dcs_t('admin.cms.gallery_delete_failed'), 'error', $editId];
            \logAdminActivity('CMS_GALLERY_DELETE', $_SESSION['admin_id'], 'cms', $editId, []);
            return [\dcs_t('admin.cms.gallery_deleted'), 'success', ''];
        }
        if ($action !== 'save_gallery') return [\dcs_t('admin.cms.invalid_action'), 'error', $editId];

        $title = trim((string)($_POST['title'] ?? ''));
        if ($title === '' || strlen($title) > 120) return [\dcs_t('admin.cms.gallery_invalid'), 'error', $editId];
        $mediaById = [];
        foreach ($this->media->items() as $item) $mediaById[(string)$item['id']] = $item;
        $items = [];
        foreach ((array)($_POST['media_ids'] ?? []) as $mediaId) {
            $mediaId = preg_replace('/[^a-f0-9]/', '', (string)$mediaId);
            if (!isset($mediaById[$mediaId])) continue;
            $items[] = [
                'media_id' => $mediaId,
                'alt' => substr(trim((string)($_POST['alt'][$mediaId] ?? '')), 0, 300),
                'caption' => substr(trim((string)($_POST['caption'][$mediaId] ?? '')), 0, 300),
            ];
        }
        if (!$items) return [\dcs_t('admin.cms.gallery_images_required'), 'error', $editId];
        $existing = $editId !== '' ? $this->store->find($editId) : null;
        $now = gmdate('c');
        $gallery = [
            'id' => $existing['id'] ?? bin2hex(random_bytes(8)),
            'title' => $title,
            'enabled' => isset($_POST['enabled']),
            'columns' => max(2, min(4, (int)($_POST['columns'] ?? 3))),
            'items' => $items,
            'created_at' => $existing['created_at'] ?? $now,
            'updated_at' => $now,
        ];
        if (!$this->store->save($gallery)) return [\dcs_t('admin.cms.save_failed'), 'error', $editId];
        \logAdminActivity('CMS_GALLERY_SAVE', $_SESSION['admin_id'], 'cms', $gallery['id'], ['images' => count($items), 'enabled' => $gallery['enabled']]);
        return [\dcs_t('admin.cms.gallery_saved'), 'success', $gallery['id']];
    }
}
