<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateBackupService
{
    private AdminFilesystemService $filesystem;
    private BackupCleanupService $cleanupService;

    public function __construct(?AdminFilesystemService $filesystem = null, ?BackupCleanupService $cleanupService = null)
    {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
        $this->cleanupService = $cleanupService ?? new BackupCleanupService();
    }

    public function backupConfigurationFiles(string $rootPath, string $backupDir, callable $log): void
    {
        $log('Backing up configuration files...');
        $configBackupDir = $backupDir . '/config-backup-' . date('Ymd-His');
        if (!is_dir($configBackupDir)) {
            mkdir($configBackupDir, 0755, true);
        }

        foreach (BackupFileCatalog::configurationFiles() as $file) {
            $sourcePath = $rootPath . '/' . $file;
            if (file_exists($sourcePath)) {
                $destPath = $configBackupDir . '/' . $file;
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                if (copy($sourcePath, $destPath)) {
                    $log("Backed up: /$file");
                } else {
                    $log("Failed to backup: /$file");
                }
            }
        }

        $log("Config backup complete: $configBackupDir");
    }

    public function createFullBackup(string $rootPath, string $backupDir, callable $log): void
    {
        $backupFile = $backupDir . '/backup-' . date('Ymd-His') . '.zip';
        $log('Creating backup...');
        $backupZip = new \ZipArchive();
        if ($backupZip->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $relPath = $this->filesystem->normalizePath(substr($filePath, strlen($rootPath) + 1));
                if (strpos($relPath, 'backups') === 0 || strpos($relPath, 'UPGRADE') === 0) {
                    continue;
                }
                if ($file->isDir()) {
                    $backupZip->addEmptyDir($relPath);
                } else {
                    $backupZip->addFile($filePath, $relPath);
                }
            }
            $backupZip->close();
            $log('Backup saved to ' . $backupFile);
            $this->cleanupService->cleanupOldBackups($backupDir, 5, $log);
        } else {
            $log('Failed to create backup');
        }
    }

}
