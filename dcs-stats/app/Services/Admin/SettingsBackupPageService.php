<?php

namespace DcsStats\Services\Admin;

final class SettingsBackupPageService
{
    private SettingsBackupDataService $backupData;

    public function __construct(?SettingsBackupDataService $backupData = null)
    {
        $this->backupData = $backupData ?? new SettingsBackupDataService();
    }

    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::language();
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::siteMetadata();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$demoRestricted) {
            [$message, $messageType] = $this->handlePost();
        }

        $backupPreview = $demoRestricted ? [] : $this->backupData->buildBackup();
        $backupPreviewLabels = [
            'excluded' => [],
            'includes' => [],
        ];
        foreach (($backupPreview['includes'] ?? []) as $item) {
            $backupPreviewLabels['includes'][$item] = $this->backupData->sectionLabel($item);
        }
        foreach (($backupPreview['excluded'] ?? []) as $item) {
            $backupPreviewLabels['excluded'][$item] = $this->backupData->sectionLabel($item);
        }

        return [
            'backupPreview' => $backupPreview,
            'backupPreviewLabels' => $backupPreviewLabels,
            'demoRestricted' => $demoRestricted,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.settings_backup.title'),
        ];
    }

    private function handlePost(): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [\dcs_t('admin.settings_backup.csrf_invalid'), 'error'];
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'export_settings') {
            $backup = $this->backupData->buildBackup();
            $fileName = 'dcs-site-settings-' . date('Y-m-d-H-i-s') . '.json';

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo json_encode($backup, JSON_PRETTY_PRINT);
            exit;
        }

        if ($action === 'import_settings') {
            return $this->handleImport();
        }

        return ['', ''];
    }

    private function handleImport(): array
    {
        if (!isset($_FILES['settings_file']) || $_FILES['settings_file']['error'] !== UPLOAD_ERR_OK) {
            return [\dcs_t('admin.settings_backup.select_file'), 'error'];
        }

        if ($_FILES['settings_file']['size'] > 2 * 1024 * 1024) {
            return [\dcs_t('admin.settings_backup.file_too_large'), 'error'];
        }

        $backup = json_decode((string)file_get_contents($_FILES['settings_file']['tmp_name']), true);
        $importError = '';

        if ($this->backupData->importBackup($backup, $importError)) {
            \logAdminActivity('SITE_SETTINGS_IMPORT', $_SESSION['admin_id'], 'settings', 'site_settings_backup', [
                'schema_version' => $backup['schema_version'] ?? null,
                'exported_at' => $backup['exported_at'] ?? null,
            ]);

            return [\dcs_t('admin.settings_backup.restore_success'), 'success'];
        }

        return [$importError ?: \dcs_t('admin.settings_backup.invalid_file'), 'error'];
    }
}
