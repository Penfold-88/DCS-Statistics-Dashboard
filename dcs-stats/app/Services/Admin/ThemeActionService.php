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

        $themePresetActionService = new ThemePresetActionService();
        $themeSettingsActionService = new ThemeSettingsActionService();
        $themeMenuActionService = new ThemeMenuActionService();
        $themeUploadService = new ThemeUploadService();
        $themeColorActionService = new ThemeColorActionService();

        switch ($action) {
            case 'apply_theme_preset':
                $presetResult = $themePresetActionService->apply($post);
                if ($presetResult['success']) {
                    $message = $presetResult['message'];
                    $this->log('THEME_PRESET_APPLY', $presetResult['log_message'] ?? $presetResult['message']);
                } else {
                    $error = $presetResult['message'];
                }
                break;

            case 'save_theme_preset':
                $savePresetResult = $themePresetActionService->save($post);
                if ($savePresetResult['success']) {
                    $message = $savePresetResult['message'];
                    $this->log('THEME_PRESET_SAVE', $savePresetResult['log_message'] ?? $savePresetResult['message']);
                } else {
                    $error = $savePresetResult['message'];
                }
                break;

            case 'delete_theme_preset':
                $deletePresetResult = $themePresetActionService->delete($post);
                if ($deletePresetResult['success']) {
                    $message = $deletePresetResult['message'];
                    $this->log('THEME_PRESET_DELETE', $deletePresetResult['log_message'] ?? $deletePresetResult['message']);
                } else {
                    $error = $deletePresetResult['message'];
                }
                break;

            case 'export_theme_settings':
                $themeSettingsActionService->export($menuConfigFile);

            case 'import_theme_settings':
                $importResult = $themeSettingsActionService->import($files, $menuConfigFile);
                if ($importResult['success']) {
                    $message = $importResult['message'];
                    $this->log('THEME_SETTINGS_IMPORT', $importResult['log_message'] ?? $importResult['message']);
                } else {
                    $error = $importResult['message'];
                }
                break;

            case 'update_menu':
                $menuResult = $themeMenuActionService->update($post, $menuConfigFile);
                if (!$menuResult['success']) {
                    $error = $menuResult['message'];
                } else {
                    $menuItems = $menuResult['menuItems'];
                    $message = $menuResult['message'];
                    $this->log('MENU_UPDATE', $menuResult['log_message'] ?? $menuResult['message']);
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
                if ($themeColorActionService->updateColors($post)) {
                    $message = 'Color theme updated successfully';
                    $this->log('THEME_COLORS', 'Updated theme colors');
                } else {
                    $error = 'Failed to save color theme';
                }
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
                if ($themeColorActionService->updateChartColors($post)) {
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
