<?php

namespace DcsStats\Services\Admin;

final class BackupService
{
    private AdminFilesystemService $filesystem;
    private BackupCleanupService $cleanupService;
    private BackupMetadataService $metadataService;
    private ByteFormatter $byteFormatter;

    public function __construct(
        ?AdminFilesystemService $filesystem = null,
        ?BackupCleanupService $cleanupService = null,
        ?BackupMetadataService $metadataService = null,
        ?ByteFormatter $byteFormatter = null
    ) {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
        $this->cleanupService = $cleanupService ?? new BackupCleanupService();
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
        if ($filename === '') {
            return ['success' => false, 'error' => 'No backup specified'];
        }

        if (!preg_match('/^backup-\d{8}-\d{6}(?:-[A-Za-z0-9_.-]+-[A-Za-z0-9_.-]+)?\.zip$/', $filename)) {
            return ['success' => false, 'error' => 'Invalid backup filename'];
        }

        $backupDir = DCS_ROOT_PATH . '/backups';
        $backupDirReal = realpath($backupDir);
        $backupFileReal = realpath($backupDir . '/' . $filename);

        if ($backupDirReal === false || $backupFileReal === false || !is_file($backupFileReal)) {
            return ['success' => false, 'error' => 'Backup not found'];
        }

        $backupDirPrefix = rtrim($backupDirReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strpos($backupFileReal, $backupDirPrefix) !== 0) {
            return ['success' => false, 'error' => 'Invalid backup path'];
        }

        if (!unlink($backupFileReal)) {
            return ['success' => false, 'error' => 'Failed to delete backup'];
        }

        \DcsStats\Core\AdminBootstrap::panel();
        $currentAdmin = getCurrentAdmin();
        logAdminAction('BACKUP_DELETE', [
            'backup' => $filename,
            'admin' => $currentAdmin['username'] ?? 'Unknown',
        ]);

        return ['success' => true];
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

        $this->addConfigFilesToBackup($backupZip, $log);
        $fileCount = $this->addProjectFilesToBackup($backupZip, $log);

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

    private function addConfigFilesToBackup(\ZipArchive $backupZip, callable $log): void
    {
        $log('Backing up configuration files...');
        foreach (BackupFileCatalog::packageConfigurationFiles() as $configFile) {
            $configPath = DCS_ROOT_PATH . '/' . $configFile;
            if (file_exists($configPath)) {
                $backupZip->addFile($configPath, $configFile);
                $log("✓ Config: $configFile");
            }
        }
    }

    private function addProjectFilesToBackup(\ZipArchive $backupZip, callable $log): int
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(DCS_ROOT_PATH, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $fileCount = 0;
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relPath = $this->filesystem->normalizePath(substr($filePath, strlen(DCS_ROOT_PATH) + 1));

            if ($this->filesystem->shouldPreservePath($relPath, BackupFileCatalog::projectBackupExcludes())) {
                continue;
            }

            if ($file->isDir()) {
                $backupZip->addEmptyDir($relPath);
            } else {
                $backupZip->addFile($filePath, $relPath);
                $fileCount++;
                if ($fileCount % 100 === 0) {
                    $log("Backed up $fileCount files...");
                }
            }
        }

        return $fileCount;
    }

}
