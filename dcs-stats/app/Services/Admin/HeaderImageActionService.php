<?php

namespace DcsStats\Services\Admin;

final class HeaderImageActionService
{
    private ThemeAssetService $assetService;
    private ImageUploadService $imageUploadService;

    public function __construct(?ThemeAssetService $assetService = null, ?ImageUploadService $imageUploadService = null)
    {
        $this->assetService = $assetService ?? new ThemeAssetService();
        $this->imageUploadService = $imageUploadService ?? new ImageUploadService();
    }

    public function update(array $post, array $files): array
    {
        $headerSettings = $this->assetService->loadHeaderImageSettings();
        $headerSettings['position_x'] = (int)($post['position_x'] ?? 50);
        $headerSettings['position_y'] = (int)($post['position_y'] ?? 50);
        $headerSettings['background_position_x'] = (int)($post['background_position_x'] ?? 50);
        $headerSettings['background_position_y'] = (int)($post['background_position_y'] ?? 50);
        $headerSettings['background_zoom'] = (int)($post['background_zoom'] ?? 125);

        if (isset($post['use_default_header_image'])) {
            $headerSettings['image'] = $this->assetService->defaultHeaderImageSettings()['image'];
        }
        if (isset($post['remove_background_image'])) {
            $headerSettings['background_image'] = '';
        }

        $headerSettings['branding_mode'] = $post['branding_mode'] ?? 'text';
        $headerSettings['logo_height'] = (int)($post['logo_height'] ?? 72);
        if (isset($post['remove_header_logo'])) {
            $headerSettings['logo'] = '';
            if ($headerSettings['branding_mode'] === 'logo') {
                $headerSettings['branding_mode'] = 'text';
            }
        }

        $uploadResult = $this->applyUploads($post, $files, $headerSettings);
        if (!$uploadResult['success']) {
            return $uploadResult;
        }

        if ($this->assetService->saveHeaderImageSettings($headerSettings)) {
            return ['success' => true, 'message' => 'Header image settings updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to save header image settings'];
    }

    private function applyUploads(array $post, array $files, array &$headerSettings): array
    {
        $uploads = [
            ['skip' => isset($post['use_default_header_image']), 'file' => 'header_image', 'prefix' => 'header-image', 'limit' => 5242880, 'label' => 'Header image', 'setting' => 'image'],
            ['skip' => isset($post['remove_background_image']), 'file' => 'background_image', 'prefix' => 'page-background', 'limit' => 5242880, 'label' => 'Page background image', 'setting' => 'background_image'],
            ['skip' => isset($post['remove_header_logo']), 'file' => 'header_logo', 'prefix' => 'header-logo', 'limit' => 2097152, 'label' => 'Header logo', 'setting' => 'logo'],
        ];

        foreach ($uploads as $upload) {
            if ($upload['skip']) {
                continue;
            }

            $result = $this->imageUploadService->upload(
                $files[$upload['file']] ?? null,
                $upload['prefix'],
                $upload['limit'],
                $upload['label'],
                false
            );
            if (!$result['success']) {
                return $result;
            }
            if (!empty($result['path'])) {
                $headerSettings[$upload['setting']] = $result['path'];
            }
        }

        return ['success' => true];
    }
}
