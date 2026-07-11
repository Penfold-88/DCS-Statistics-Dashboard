<?php

namespace DcsStats\Services\Admin;

final class BackupCreationService
{
    private BackupArchiveBuilder $archiveBuilder;
    private BackupCleanupService $cleanupService;
    private ByteFormatter $byteFormatter;

    public function __construct(
        ?BackupArchiveBuilder $archiveBuilder = null,
        ?BackupCleanupService $cleanupService = null,
        ?ByteFormatter $byteFormatter = null
    ) {
        $this->archiveBuilder = $archiveBuilder ?? new BackupArchiveBuilder();
        $this->cleanupService = $cleanupService ?? new BackupCleanupService();
        $this->byteFormatter = $byteFormatter ?? new ByteFormatter();
    }

    public function create(callable $log): void
    {
        \DcsStats\Core\AdminBootstrap::panel();
        \DcsStats\Core\SupportBootstrap::versionTracker();

        $backupDir = DCS_ROOT_PATH . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0700, true);
        }
        @chmod($backupDir, 0700);

        $versionInfo = \getCurrentVersionInfo();
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
        $currentAdmin = \getCurrentAdmin();
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
        @chmod($backupFile, 0600);

        $sizeFormatted = $this->byteFormatter->format((int)filesize($backupFile));
        $log('Backup complete!');
        $log("Total files: $fileCount");
        $log("Backup size: $sizeFormatted");

        \logAdminAction('BACKUP_CREATE', [
            'backup' => $backupName . '.zip',
            'size' => $sizeFormatted,
            'admin' => $currentAdmin['username'] ?? 'Unknown',
        ]);

        $this->cleanupService->cleanupOldBackups($backupDir, 5, $log);
    }
}
