<?php

namespace DcsStats\Services\Admin;

final class BackupRestoreService
{
    private AdminFilesystemService $filesystem;
    private PreRestoreBackupService $preRestoreBackupService;

    public function __construct(?AdminFilesystemService $filesystem = null, ?PreRestoreBackupService $preRestoreBackupService = null)
    {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
        $this->preRestoreBackupService = $preRestoreBackupService ?? new PreRestoreBackupService($this->filesystem);
    }

    public function restoreBackup(string $filename, callable $log): void
    {
        \DcsStats\Core\AdminBootstrap::panel();

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

        if (!$this->preRestoreBackupService->create($rootPath, $log)) {
            $log('Restore cancelled to avoid changing live files without a recovery point.');
            return;
        }

        $this->filesystem->removeDirectory($restoreDir);
        $this->filesystem->removeDirectory($rollbackDir);
        if (!$this->filesystem->ensureDirectory($restoreDir) || !$this->filesystem->ensureDirectory($rollbackDir)) {
            $log('Error: Could not create restore workspace');
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($backupFile) !== true) {
            $log('Error: Failed to open backup file');
            return;
        }

        if (!$this->filesystem->archiveHasSafePaths($zip)) {
            $zip->close();
            $this->filesystem->removeDirectory($restoreDir);
            $this->filesystem->removeDirectory($rollbackDir);
            $log('Error: Backup contains unsafe file paths');
            return;
        }

        $log('Extracting backup...');
        if (!$zip->extractTo($restoreDir)) {
            $zip->close();
            $this->filesystem->removeDirectory($restoreDir);
            $this->filesystem->removeDirectory($rollbackDir);
            $log('Error: Failed to extract backup');
            return;
        }
        $zip->close();

        $restoredFiles = $this->restoreExtractedFiles($rootPath, $restoreDir, $rollbackDir, $log);
        if ($restoredFiles === null) {
            return;
        }

        $log('Cleaning up...');
        $this->filesystem->removeDirectory($restoreDir);
        $this->filesystem->removeDirectory($rollbackDir);

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
                $relPath = $this->filesystem->normalizePath(substr($filePath, strlen($restoreDir) + 1));
                $targetPath = $rootPath . '/' . $relPath;

                if ($this->filesystem->shouldPreservePath($relPath, $preserve)) {
                    continue;
                }

                if ($file->isDir()) {
                    if (!$this->filesystem->ensureDirectory($targetPath)) {
                        throw new \RuntimeException("Failed to create directory: $relPath");
                    }
                    continue;
                }

                if (!$this->filesystem->ensureDirectory(dirname($targetPath))) {
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
            $this->filesystem->removeDirectory($restoreDir);
            $this->filesystem->removeDirectory($rollbackDir);
            $log('Restore failed. Live files have been rolled back where changes were made.');
            return null;
        }

        return $restoredFiles;
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

        if (!$this->filesystem->ensureDirectory(dirname($rollbackPath))) {
            return false;
        }

        return copy($targetPath, $rollbackPath);
    }

    private function rollbackRestoreChanges(array $changedFiles): void
    {
        foreach (array_reverse($changedFiles) as $change) {
            if ($change['existed']) {
                $this->filesystem->ensureDirectory(dirname($change['target']));
                copy($change['rollback'], $change['target']);
            } elseif (file_exists($change['target'])) {
                unlink($change['target']);
            }
        }
    }
}
