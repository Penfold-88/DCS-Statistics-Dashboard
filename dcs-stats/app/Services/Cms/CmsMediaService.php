<?php

namespace DcsStats\Services\Cms;

final class CmsMediaService
{
    private const MAX_BYTES = 2097152;
    private const MAX_DIMENSION = 6000;
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private CmsMediaStore $store;
    private CmsPageStore $pageStore;

    public function __construct(?CmsMediaStore $store = null, ?CmsPageStore $pageStore = null)
    {
        $this->store = $store ?? new CmsMediaStore();
        $this->pageStore = $pageStore ?? new CmsPageStore();
    }

    public function items(): array
    {
        return array_values(array_filter($this->store->all(), function (array $item): bool {
            return $this->absolutePath((string)($item['path'] ?? '')) !== null;
        }));
    }

    public function upload(?array $file): array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_upload_failed')];
        }
        $tmp = (string)($file['tmp_name'] ?? '');
        $bytes = (int)($file['size'] ?? 0);
        if ($tmp === '' || !is_uploaded_file($tmp) || $bytes < 1 || $bytes > self::MAX_BYTES) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_size_invalid')];
        }

        $dimensions = @getimagesize($tmp);
        $mime = is_array($dimensions) ? (string)($dimensions['mime'] ?? '') : '';
        if (class_exists('finfo')) {
            $detectedMime = (string)(new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
            if ($detectedMime !== $mime) {
                return ['success' => false, 'message' => \dcs_t('admin.cms.media_type_invalid')];
            }
        }
        if (!isset(self::TYPES[$mime]) || !$dimensions) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_type_invalid')];
        }
        $width = (int)$dimensions[0];
        $height = (int)$dimensions[1];
        if ($width < 1 || $height < 1 || $width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_dimensions_invalid')];
        }

        $directory = DCS_ROOT_PATH . '/uploads/pages';
        if (!is_dir($directory) && !@mkdir($directory, 0755, true)) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_storage_failed')];
        }
        $id = bin2hex(random_bytes(16));
        $filename = $id . '.' . self::TYPES[$mime];
        $absolute = $directory . '/' . $filename;
        if (!move_uploaded_file($tmp, $absolute)) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_storage_failed')];
        }
        @chmod($absolute, 0644);

        $item = [
            'id' => $id,
            'path' => 'uploads/pages/' . $filename,
            'original_name' => $this->cleanOriginalName((string)($file['name'] ?? 'image')),
            'mime' => $mime,
            'bytes' => $bytes,
            'width' => $width,
            'height' => $height,
            'uploaded_at' => gmdate('c'),
        ];
        if (!$this->store->add($item)) {
            @unlink($absolute);
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_storage_failed')];
        }
        return ['success' => true, 'item' => $item];
    }

    public function delete(string $id): array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $id)) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_not_found')];
        }
        $item = $this->store->find($id);
        if (!$item) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_not_found')];
        }
        $path = (string)($item['path'] ?? '');
        foreach ($this->pageStore->all() as $page) {
            if ($path !== '' && (strpos((string)($page['content'] ?? ''), $path) !== false || ($page['seo_image'] ?? '') === $path)) {
                return ['success' => false, 'message' => \dcs_t('admin.cms.media_in_use'), 'in_use' => true];
            }
        }
        $absolute = $this->absolutePath($path);
        if ($absolute !== null && !@unlink($absolute)) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_delete_failed')];
        }
        if (!$this->store->delete($id)) {
            return ['success' => false, 'message' => \dcs_t('admin.cms.media_delete_failed')];
        }
        return ['success' => true, 'message' => \dcs_t('admin.cms.media_deleted')];
    }

    private function absolutePath(string $relative): ?string
    {
        if (!preg_match('#^uploads/pages/[a-f0-9]{32}\.(jpg|png|webp)$#', $relative)) {
            return null;
        }
        $path = DCS_ROOT_PATH . '/' . $relative;
        return is_file($path) ? $path : null;
    }

    private function cleanOriginalName(string $name): string
    {
        $name = trim((string)preg_replace('/[^A-Za-z0-9._ -]+/', '', basename($name)));
        return substr($name !== '' ? $name : 'image', 0, 120);
    }
}
