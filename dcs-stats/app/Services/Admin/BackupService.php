<?php

namespace DcsStats\Services\Admin;

final class BackupService
{
    private BackupArchiveBuilder $archiveBuilder;
    private BackupCleanupService $cleanupService;
    private BackupDeletionService $deletionService;
    private BackupMetadataService $metadataService;
    private ByteFormatter $byteFormatter;

    public function __construct(
        ?AdminFilesystemService $filesystem = null,
        ?BackupCleanupService $cleanupService = null,
        ?BackupMetadataService $metadataService = null,
        ?ByteFormatter $byteFormatter = null,
        ?BackupArchiveBuilder $archiveBuilder = null,
        ?BackupDeletionService $deletionService = null
    ) {
        $this->archiveBuilder = $archiveBuilder ?? new BackupArchiveBuilder($filesystem);
        $this->cleanupService = $cleanupService ?? new BackupCleanupService();
        $this->deletionService = $deletionService ?? new BackupDeletionService();
        $this->metadataService = $metadataService ?? new BackupMetadataService();
        $this->byteFormatter = $byteFormatter ?? new ByteFormatter();
    }

    public function listBackups(): array
    {
        $backupDir = DCS_ROOT_PATH . '/backups';
        $backups = [];

        if (!is_dir($backupDir)) {
            return $backups;
        }

        $files = glob($backupDir . '/backup-*.zip') ?: [];
        foreach ($files as $file) {
            $filename = basename($file);
            $metadata = $this->metadataService->read($file, $filename);

            $backups[] = [
                'name' => $filename,
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'size' => $this->byteFormatter->format((int)filesize($file)),
                'version' => $metadata['version'],
                'branch' => $metadata['branch'],
            ];
        }

        usort($backups, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }

    public function deleteBackup(string $filename): array
    {
        return $this->deletionService->delete($filename);
    }

    public function createBackup(callable $log): void
    {
        \DcsStats\Core\AdminBootstrap::panel();
        \DcsStats\Core\SupportBootstrap::versionTracker();

        $backupDir = DCS_ROOT_PATH . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $versionInfo = getCurrentVersionInfo();
        $currentVersion = $versionInfo['version'] ?? (defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : '1.0.0');
        $currentBranch = $versionInfo['branch'] ?? 'main';
        $safeVersion = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $currentVersion);
        $backupName = 'backup-' . date('Ymd-His') . '-' . $currentBranch . '-' . $safeVersion;
        $backupFile = $backupDir . '/' . $backupName . '.zip';

        $log("Creating backup: $backupName");
        $log("Version: $currentVersion");
        $log("Branch: $currentBranch");

        $backupZip = new \ZipArchive();
        if ($backupZip->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $log('Error: Failed to create backup');
            return;
        }

        $fileCount = $this->archiveBuilder->populate($backupZip, $log);

        $currentAdmin = getCurrentAdmin();
        $metadata = [
            'version' => $currentVersion,
            'branch' => $currentBranch,
            'commit_sha' => $versionInfo['commit_sha'] ?? null,
            'commit_date' => $versionInfo['commit_date'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $currentAdmin['username'] ?? 'Unknown',
        ];
        $backupZip->addFromString('.backup_meta.json', json_encode($metadata, JSON_PRETTY_PRINT));
        $backupZip->close();

        $sizeFormatted = $this->byteFormatter->format((int)filesize($backupFile));

        $log('Backup complete!');
        $log("Total files: $fileCount");
        $log("Backup size: $sizeFormatted");

        logAdminAction('BACKUP_CREATE', [
            'backup' => $backupName . '.zip',
            'size' => $sizeFormatted,
            'admin' => $currentAdmin['username'] ?? 'Unknown',
        ]);

        $this->cleanupService->cleanupOldBackups($backupDir, 5, $log);
    }

}
