<?php

namespace DcsStats\Services\Admin;

final class BackupRestoreService
{
    public function restoreBackup(string $filename, callable $log): void
    {
        require_once DCS_ROOT_PATH . '/site-config/admin_functions.php';

        if ($filename === '') {
            $log('Error: No backup specified');
            return;
        }

        if (!preg_match('/^backup-\d{8}-\d{6}(?:-[A-Za-z0-9_.-]+-[A-Za-z0-9_.-]+)?\.zip$/', $filename)) {
            $log('Error: Invalid backup filename');
            return;
        }

        $rootPath = DCS_ROOT_PATH;
        $backupFile = $rootPath . '/backups/' . $filename;
        $restoreDir = $rootPath . '/RESTORE_TEMP';
        $rollbackDir = $rootPath . '/RESTORE_ROLLBACK';

        if (!file_exists($backupFile)) {
            $log('Error: Backup file not found');
            return;
        }

        $log("Starting restore from: $filename");

        if (!$this->createPreRestoreBackup($rootPath, $log)) {
            $log('Restore cancelled to avoid changing live files without a recovery point.');
            return;
        }

        $this->removeDirectory($restoreDir);
        $this->removeDirectory($rollbackDir);
        if (!$this->ensureDirectory($restoreDir) || !$this->ensureDirectory($rollbackDir)) {
            $log('Error: Could not create restore workspace');
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($backupFile) !== true) {
            $log('Error: Failed to open backup file');
            return;
        }

        if (!$this->validateBackupZip($zip)) {
            $zip->close();
            $this->removeDirectory($restoreDir);
            $this->removeDirectory($rollbackDir);
            $log('Error: Backup contains unsafe file paths');
            return;
        }

        $log('Extracting backup...');
        if (!$zip->extractTo($restoreDir)) {
            $zip->close();
            $this->removeDirectory($restoreDir);
            $this->removeDirectory($rollbackDir);
            $log('Error: Failed to extract backup');
            return;
        }
        $zip->close();

        $restoredFiles = $this->restoreExtractedFiles($rootPath, $restoreDir, $rollbackDir, $log);
        if ($restoredFiles === null) {
            return;
        }

        $log('Cleaning up...');
        $this->removeDirectory($restoreDir);
        $this->removeDirectory($rollbackDir);

        $currentAdmin = getCurrentAdmin();
        logAdminAction('BACKUP_RESTORE', [
            'backup' => $filename,
            'restored_files' => $restoredFiles,
            'admin' => $currentAdmin['username'] ?? 'Unknown',
        ]);

        $log('Restore complete!');
        $log('Please refresh your browser to see the restored version.');
    }

    private function restoreExtractedFiles(string $rootPath, string $restoreDir, string $rollbackDir, callable $log): ?int
    {
        $preserve = [
            'backups',
            'RESTORE_TEMP',
            'site-config/data',
            'api_config.json',
            'site_config.json',
        ];

        $log('Restoring files...');
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($restoreDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $changedFiles = [];
        $restoredFiles = 0;

        try {
            foreach ($iterator as $file) {
                $filePath = $file->getRealPath();
                $relPath = $this->normalizePath(substr($filePath, strlen($restoreDir) + 1));
                $targetPath = $rootPath . '/' . $relPath;

                if ($this->shouldPreservePath($relPath, $preserve)) {
                    continue;
                }

                if ($file->isDir()) {
                    if (!$this->ensureDirectory($targetPath)) {
                        throw new \RuntimeException("Failed to create directory: $relPath");
                    }
                    continue;
                }

                if (!$this->ensureDirectory(dirname($targetPath))) {
                    throw new \RuntimeException("Failed to create parent directory: $relPath");
                }

                if (!$this->backupTargetBeforeRestore($targetPath, $relPath, $rollbackDir, $changedFiles)) {
                    throw new \RuntimeException("Failed to stage rollback copy: $relPath");
                }

                if (!copy($filePath, $targetPath)) {
                    throw new \RuntimeException("Failed to restore file: $relPath");
                }

                $restoredFiles++;
                $log("Restored: $relPath");
            }
        } catch (\Throwable $e) {
            $log('Error: ' . $e->getMessage());
            $log('Rolling back changed files...');
            $this->rollbackRestoreChanges($changedFiles);
            $this->removeDirectory($restoreDir);
            $this->removeDirectory($rollbackDir);
            $log('Restore failed. Live files have been rolled back where changes were made.');
            return null;
        }

        return $restoredFiles;
    }

    private function createPreRestoreBackup(string $rootPath, callable $log): bool
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
            $relPath = $this->normalizePath(substr($filePath, strlen($rootPath) + 1));

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

    private function validateBackupZip(\ZipArchive $zip): bool
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$this->isSafeZipPath((string)$name)) {
                return false;
            }
        }

        return true;
    }

    private function isSafeZipPath(string $path): bool
    {
        $path = $this->normalizePath($path);

        return $path !== '' &&
            $path[0] !== '/' &&
            strpos($path, '../') === false &&
            strpos($path, '/..') === false &&
            strpos($path, ':') === false;
    }

    private function shouldPreservePath(string $relPath, array $preserve): bool
    {
        $relPath = $this->normalizePath($relPath);
        foreach ($preserve as $path) {
            $path = $this->normalizePath($path);
            if ($relPath === $path || strpos($relPath, $path . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    private function backupTargetBeforeRestore(string $targetPath, string $relPath, string $rollbackDir, array &$changedFiles): bool
    {
        if (isset($changedFiles[$relPath])) {
            return true;
        }

        $rollbackPath = $rollbackDir . '/' . $relPath;
        $changedFiles[$relPath] = [
            'target' => $targetPath,
            'rollback' => $rollbackPath,
            'existed' => file_exists($targetPath),
        ];

        if (!file_exists($targetPath)) {
            return true;
        }

        if (!$this->ensureDirectory(dirname($rollbackPath))) {
            return false;
        }

        return copy($targetPath, $rollbackPath);
    }

    private function rollbackRestoreChanges(array $changedFiles): void
    {
        foreach (array_reverse($changedFiles) as $change) {
            if ($change['existed']) {
                $this->ensureDirectory(dirname($change['target']));
                copy($change['rollback'], $change['target']);
            } elseif (file_exists($change['target'])) {
                unlink($change['target']);
            }
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    private function ensureDirectory(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }

        return mkdir($dir, 0755, true);
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
