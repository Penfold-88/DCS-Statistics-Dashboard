<?php

namespace DcsStats\Services\Admin;

final class ThemeCssBackupService
{
    public function upload(array $files): array
    {
        if (!isset($files['css_file']) || $files['css_file']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Please select a CSS file to upload'];
        }

        $uploadedFile = $files['css_file'];
        $fileName = $uploadedFile['name'];
        $fileTmp = $uploadedFile['tmp_name'];
        $fileSize = $uploadedFile['size'];
        $fileType = mime_content_type($fileTmp);

        if (!in_array($fileType, ['text/css', 'text/plain'], true) || !preg_match('/\.css$/i', $fileName)) {
            return ['success' => false, 'message' => 'Please upload a valid CSS file'];
        }

        if ($fileSize > 1048576) {
            return ['success' => false, 'message' => 'CSS file size must be less than 1MB'];
        }

        $currentCss = DCS_ROOT_PATH . '/styles.css';
        $backupDir = $this->backupDirectory();
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        copy($currentCss, $backupDir . '/styles_' . date('Y-m-d_H-i-s') . '.css');

        if (move_uploaded_file($fileTmp, $currentCss)) {
            return [
                'success' => true,
                'message' => 'CSS file uploaded successfully. Previous version backed up.',
                'filename' => $fileName,
            ];
        }

        return ['success' => false, 'message' => 'Failed to upload CSS file'];
    }

    public function restore(string $backupFile): array
    {
        $backupPath = $this->backupDirectory() . '/' . basename($backupFile);

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

    public function list(): array
    {
        $backupDir = $this->backupDirectory();
        if (!is_dir($backupDir)) {
            return [];
        }

        $backups = [];
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

    private function backupDirectory(): string
    {
        return DCS_ROOT_PATH . '/site-config/theme_backups';
    }
}
