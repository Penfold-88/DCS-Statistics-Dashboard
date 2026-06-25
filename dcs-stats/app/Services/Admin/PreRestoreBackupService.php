<?php

namespace DcsStats\Services\Admin;

final class PreRestoreBackupService
{
    private AdminFilesystemService $filesystem;

    public function __construct(?AdminFilesystemService $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
    }

    public function create(string $rootPath, callable $log): bool
    {
        $backupDir = $rootPath . '/backups';
        if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true)) {
            $log('Error: Could not create backup directory before restore');
            return false;
        }

        $backupFile = $backupDir . '/pre-restore-' . date('Ymd-His') . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $log('Error: Could not create pre-restore backup');
            return false;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relPath = $this->filesystem->normalizePath(substr($filePath, strlen($rootPath) + 1));

            if (
                strpos($relPath, 'backups/') === 0 ||
                strpos($relPath, 'RESTORE_TEMP/') === 0 ||
                strpos($relPath, 'UPGRADE/') === 0
            ) {
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relPath);
            } else {
                $zip->addFile($filePath, $relPath);
            }
        }

        $zip->close();
        $log('Pre-restore backup created: ' . basename($backupFile));

        return true;
    }
}
