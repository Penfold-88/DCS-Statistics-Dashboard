<?php

namespace DcsStats\Services\Admin;

final class ThemeUploadService
{
    private ThemeAssetService $assetService;

    public function __construct(?ThemeAssetService $assetService = null)
    {
        $this->assetService = $assetService ?? new ThemeAssetService();
    }

    public function uploadCss(array $files): array
    {
        if (!isset($files['css_file']) || $files['css_file']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Please select a CSS file to upload'];
        }

        $uploadedFile = $files['css_file'];
        $fileName = $uploadedFile['name'];
        $fileTmp = $uploadedFile['tmp_name'];
        $fileSize = $uploadedFile['size'];
        $allowedTypes = ['text/css', 'text/plain'];
        $fileType = mime_content_type($fileTmp);

        if (!in_array($fileType, $allowedTypes, true) || !preg_match('/\.css$/i', $fileName)) {
            return ['success' => false, 'message' => 'Please upload a valid CSS file'];
        }

        if ($fileSize > 1048576) {
            return ['success' => false, 'message' => 'CSS file size must be less than 1MB'];
        }

        $currentCss = DCS_ROOT_PATH . '/styles.css';
        $backupDir = DCS_ROOT_PATH . '/site-config/theme_backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFile = $backupDir . '/styles_' . date('Y-m-d_H-i-s') . '.css';
        copy($currentCss, $backupFile);

        if (move_uploaded_file($fileTmp, $currentCss)) {
            return [
                'success' => true,
                'message' => 'CSS file uploaded successfully. Previous version backed up.',
                'filename' => $fileName,
            ];
        }

        return ['success' => false, 'message' => 'Failed to upload CSS file'];
    }

    public function restoreBackup(string $backupFile): array
    {
        $backupPath = DCS_ROOT_PATH . '/site-config/theme_backups/' . basename($backupFile);

        if (!file_exists($backupPath)) {
            return ['success' => false, 'message' => 'Backup file not found'];
        }

        copy($backupPath, DCS_ROOT_PATH . '/styles.css');

        return [
            'success' => true,
            'message' => 'Theme restored from backup',
            'filename' => basename($backupFile),
        ];
    }

    public function updateHeaderImageSettings(array $post, array $files): array
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

        if (!isset($post['use_default_header_image'])) {
            $uploadResult = $this->uploadImage($files['header_image'] ?? null, 'header-image', 5242880, 'Header image', false);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            if (!empty($uploadResult['path'])) {
                $headerSettings['image'] = $uploadResult['path'];
            }
        }

        if (!isset($post['remove_background_image'])) {
            $uploadResult = $this->uploadImage($files['background_image'] ?? null, 'page-background', 5242880, 'Page background image', false);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            if (!empty($uploadResult['path'])) {
                $headerSettings['background_image'] = $uploadResult['path'];
            }
        }

        if (!isset($post['remove_header_logo'])) {
            $uploadResult = $this->uploadImage($files['header_logo'] ?? null, 'header-logo', 2097152, 'Header logo', false);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            if (!empty($uploadResult['path'])) {
                $headerSettings['logo'] = $uploadResult['path'];
            }
        }

        if ($this->assetService->saveHeaderImageSettings($headerSettings)) {
            return ['success' => true, 'message' => 'Header image settings updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to save header image settings'];
    }

    private function uploadImage(?array $uploadedFile, string $targetBaseName, int $maxBytes, string $label, bool $required): array
    {
        if (!$uploadedFile || ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required
                ? ['success' => false, 'message' => $label . ' is required']
                : ['success' => true, 'path' => ''];
        }

        if (($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Failed to upload ' . strtolower($label)];
        }

        $tmpPath = $uploadedFile['tmp_name'];
        $fileSize = $uploadedFile['size'];
        $fileType = mime_content_type($tmpPath);
        $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        $allowedTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];

        if (!isset($allowedTypes[$extension]) || $allowedTypes[$extension] !== $fileType) {
            return ['success' => false, 'message' => 'Please upload a JPG, PNG, or WebP ' . strtolower(str_replace('Header ', '', $label))];
        }

        if ($fileSize > $maxBytes) {
            $limit = $maxBytes >= 5242880 ? '5MB' : '2MB';
            return ['success' => false, 'message' => $label . ' must be less than ' . $limit];
        }

        $uploadDir = DCS_ROOT_PATH . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetName = $targetBaseName . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
        $targetPath = $uploadDir . '/' . $targetName;
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            return ['success' => false, 'message' => 'Failed to upload ' . strtolower($label)];
        }

        return ['success' => true, 'path' => 'uploads/' . $targetName];
    }

    public function listBackups(): array
    {
        $backupDir = DCS_ROOT_PATH . '/site-config/theme_backups';
        $backups = [];

        if (!is_dir($backupDir)) {
            return [];
        }

        foreach (scandir($backupDir) as $file) {
            if (preg_match('/^styles_.*\.css$/', $file)) {
                $path = $backupDir . '/' . $file;
                $backups[] = [
                    'filename' => $file,
                    'date' => filemtime($path),
                    'size' => filesize($path),
                ];
            }
        }

        usort($backups, static fn($a, $b) => $b['date'] - $a['date']);

        return $backups;
    }
}
