<?php

namespace DcsStats\Services\Admin;

final class ThemeAppearanceActionDispatcher
{
    private ThemeUploadService $uploadActions;
    private ThemeColorActionService $colorActions;
    private ThemeActionLogger $logger;

    public function __construct(
        ?ThemeUploadService $uploadActions = null,
        ?ThemeColorActionService $colorActions = null,
        ?ThemeActionLogger $logger = null
    ) {
        $this->uploadActions = $uploadActions ?? new ThemeUploadService();
        $this->colorActions = $colorActions ?? new ThemeColorActionService();
        $this->logger = $logger ?? new ThemeActionLogger();
    }

    public function handle(string $action, array $post, array $files, bool $isAirBoss, array $menuItems): array
    {
        switch ($action) {
            case 'upload_css':
                if (!$isAirBoss) {
                    return $this->error('Only Air Boss can upload custom CSS files', $menuItems);
                }
                $result = $this->uploadActions->uploadCss($files);
                return $this->result($result, 'THEME_UPLOAD', 'Uploaded new CSS file: ' . ($result['filename'] ?? 'unknown'), $menuItems);

            case 'update_colors':
                if ($this->colorActions->updateColors($post)) {
                    $this->logger->log('THEME_COLORS', 'Updated theme colors');
                    return $this->success('Color theme updated successfully', $menuItems);
                }
                return $this->error('Failed to save color theme', $menuItems);

            case 'update_header_image':
                $result = $this->uploadActions->updateHeaderImageSettings($post, $files);
                return $this->result($result, 'HEADER_IMAGE_UPDATE', 'Updated header image settings', $menuItems);

            case 'update_chart_colors':
                if ($this->colorActions->updateChartColors($post)) {
                    $this->logger->log('CHART_THEME_COLORS', 'Updated leaderboard chart colours');
                    return $this->success('Leaderboard chart colours updated successfully', $menuItems);
                }
                return $this->error('Failed to save leaderboard chart colours', $menuItems);

            case 'restore_backup':
                if (!$isAirBoss) {
                    return $this->error('Only Air Boss can restore theme backups', $menuItems);
                }
                $result = $this->uploadActions->restoreBackup($post['backup_file'] ?? '');
                return $this->result($result, 'THEME_RESTORE', 'Restored theme from: ' . ($result['filename'] ?? 'unknown'), $menuItems);
        }

        return $this->success('', $menuItems);
    }

    private function result(array $result, string $logAction, string $logMessage, array $menuItems): array
    {
        if ($result['success']) {
            $this->logger->log($logAction, $logMessage);
            return $this->success($result['message'], $menuItems);
        }

        return $this->error($result['message'], $menuItems);
    }

    private function success(string $message, array $menuItems): array
    {
        return ['error' => '', 'menuItems' => $menuItems, 'message' => $message];
    }

    private function error(string $error, array $menuItems): array
    {
        return ['error' => $error, 'menuItems' => $menuItems, 'message' => ''];
    }
}
