<?php

namespace DcsStats\Services\Admin;

final class ThemeSettingsActionService
{
    private ThemeSettingsBackupService $backupService;

    public function __construct(?ThemeSettingsBackupService $backupService = null)
    {
        $this->backupService = $backupService ?? new ThemeSettingsBackupService();
    }

    public function export(string $menuConfigFile): void
    {
        $backup = $this->backupService->buildBackup($menuConfigFile);
        $fileName = 'dcs-theme-settings-' . date('Y-m-d-H-i-s') . '.json';
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: no-store');
        echo json_encode($backup, JSON_PRETTY_PRINT);
        exit;
    }

    public function import(array $files, string $menuConfigFile): array
    {
        if (!isset($files['theme_settings_file']) || $files['theme_settings_file']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Please select a theme settings backup file'];
        }

        $uploadedFile = $files['theme_settings_file'];
        if ($uploadedFile['size'] > 1048576) {
            return ['success' => false, 'message' => 'Theme settings backup must be less than 1MB'];
        }

        $backup = json_decode((string)file_get_contents($uploadedFile['tmp_name']), true);
        if ($this->backupService->importBackup($backup, $menuConfigFile)) {
            return [
                'success' => true,
                'message' => 'Theme settings restored successfully',
                'log_message' => 'Imported theme settings backup',
            ];
        }

        return ['success' => false, 'message' => 'Invalid theme settings backup file'];
    }
}
