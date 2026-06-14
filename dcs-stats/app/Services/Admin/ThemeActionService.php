<?php

namespace DcsStats\Services\Admin;

final class ThemeActionService
{
    public function handle(
        array $post,
        array $files,
        string $menuConfigFile,
        bool $isAirBoss,
        array $menuItems
    ): array {
        $message = '';
        $error = '';
        $action = $post['action'] ?? '';

        $themePresetStorageService = new ThemePresetStorageService();
        $themePresetService = new ThemePresetService();
        $themeSettingsBackupService = new ThemeSettingsBackupService();
        $themeMenuService = new ThemeMenuService();
        $themeUploadService = new ThemeUploadService();
        $themeColorService = new ThemeColorService();

        switch ($action) {
            case 'apply_theme_preset':
                $presetId = $post['preset_id'] ?? '';
                $presetType = $post['preset_type'] ?? 'built_in';
                $preset = $presetType === 'custom'
                    ? $themePresetStorageService->customPresetById($presetId)
                    : ($themePresetService->builtInPresets()[$presetId] ?? null);

                if ($preset && $themePresetService->applyPreset($preset)) {
                    $message = 'Theme preset applied successfully';
                    $this->log('THEME_PRESET_APPLY', 'Applied theme preset: ' . ($preset['name'] ?? $presetId));
                } else {
                    $error = 'Theme preset could not be applied';
                }
                break;

            case 'save_theme_preset':
                $presetName = trim($post['preset_name'] ?? '');
                $savePresetResult = $themePresetService->saveCurrentPreset($presetName);
                if ($savePresetResult['success']) {
                    $message = $savePresetResult['message'];
                    $this->log('THEME_PRESET_SAVE', 'Saved custom theme preset: ' . $presetName);
                } else {
                    $error = $savePresetResult['message'];
                }
                break;

            case 'delete_theme_preset':
                $presetIndex = (int)($post['preset_id'] ?? -1);
                $deletePresetResult = $themePresetService->deleteCustomPreset($presetIndex);
                if ($deletePresetResult['success']) {
                    $message = $deletePresetResult['message'];
                    $this->log('THEME_PRESET_DELETE', 'Deleted custom theme preset: ' . ($deletePresetResult['deleted_name'] ?? 'Custom preset'));
                } else {
                    $error = $deletePresetResult['message'];
                }
                break;

            case 'export_theme_settings':
                $backup = $themeSettingsBackupService->buildBackup($menuConfigFile);
                $fileName = 'dcs-theme-settings-' . date('Y-m-d-H-i-s') . '.json';
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                header('Cache-Control: no-store');
                echo json_encode($backup, JSON_PRETTY_PRINT);
                exit;

            case 'import_theme_settings':
                if (!isset($files['theme_settings_file']) || $files['theme_settings_file']['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Please select a theme settings backup file';
                    break;
                }

                $uploadedFile = $files['theme_settings_file'];
                if ($uploadedFile['size'] > 1048576) {
                    $error = 'Theme settings backup must be less than 1MB';
                    break;
                }

                $backup = json_decode((string)file_get_contents($uploadedFile['tmp_name']), true);
                if ($themeSettingsBackupService->importBackup($backup, $menuConfigFile)) {
                    $message = 'Theme settings restored successfully';
                    $this->log('THEME_SETTINGS_IMPORT', 'Imported theme settings backup');
                } else {
                    $error = 'Invalid theme settings backup file';
                }
                break;

            case 'update_menu':
                $newMenuItems = $themeMenuService->menuFromPost($post);
                if (!$themeMenuService->saveMenu($menuConfigFile, $newMenuItems)) {
                    $error = 'Failed to save menu configuration. Please check file permissions.';
                } else {
                    $menuItems = $newMenuItems;
                    $message = 'Menu configuration updated successfully';
                    $this->log('MENU_UPDATE', 'Updated navigation menu configuration');
                }
                break;

            case 'upload_css':
                if (!$isAirBoss) {
                    $error = 'Only Air Boss can upload custom CSS files';
                    break;
                }

                $cssUploadResult = $themeUploadService->uploadCss($files);
                if ($cssUploadResult['success']) {
                    $message = $cssUploadResult['message'];
                    $this->log('THEME_UPLOAD', 'Uploaded new CSS file: ' . ($cssUploadResult['filename'] ?? 'unknown'));
                } else {
                    $error = $cssUploadResult['message'];
                }
                break;

            case 'update_colors':
                $colors = [];
                foreach ($themeColorService->defaultColors() as $key => $defaultValue) {
                    $colors[$key] = $post[$key] ?? $defaultValue;
                }
                $themeOptions = [
                    'header_title_gradient_enabled' => isset($post['header_title_gradient_enabled']),
                    'page_background_gradient_enabled' => isset($post['page_background_gradient_enabled']),
                ];

                file_put_contents(DCS_ROOT_PATH . '/custom_theme.css', $themeColorService->buildCustomCss($colors, $themeOptions));
                $message = 'Color theme updated successfully';
                $this->log('THEME_COLORS', 'Updated theme colors');
                break;

            case 'update_header_image':
                $headerUpdateResult = $themeUploadService->updateHeaderImageSettings($post, $files);
                if ($headerUpdateResult['success']) {
                    $message = $headerUpdateResult['message'];
                    $this->log('HEADER_IMAGE_UPDATE', 'Updated header image settings');
                } else {
                    $error = $headerUpdateResult['message'];
                }
                break;

            case 'update_chart_colors':
                $chartTheme = [];
                foreach (\getDefaultChartTheme() as $key => $defaultValue) {
                    $chartTheme[$key] = $post[$key] ?? $defaultValue;
                }

                if (\saveChartTheme($chartTheme)) {
                    $message = 'Leaderboard chart colours updated successfully';
                    $this->log('CHART_THEME_COLORS', 'Updated leaderboard chart colours');
                } else {
                    $error = 'Failed to save leaderboard chart colours';
                }
                break;

            case 'restore_backup':
                if (!$isAirBoss) {
                    $error = 'Only Air Boss can restore theme backups';
                    break;
                }

                $restoreResult = $themeUploadService->restoreBackup($post['backup_file'] ?? '');
                if ($restoreResult['success']) {
                    $message = $restoreResult['message'];
                    $this->log('THEME_RESTORE', 'Restored theme from: ' . ($restoreResult['filename'] ?? 'unknown'));
                } else {
                    $error = $restoreResult['message'];
                }
                break;
        }

        return [
            'error' => $error,
            'menuItems' => $menuItems,
            'message' => $message,
        ];
    }

    private function log(string $action, string $message): void
    {
        if (function_exists('logActivity')) {
            \logActivity($action, $message);
        }
    }
}
