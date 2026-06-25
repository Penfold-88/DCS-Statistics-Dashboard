<?php

namespace DcsStats\Services\Admin;

final class BackupRestoreFileService
{
    private AdminFilesystemService $filesystem;

    public function __construct(?AdminFilesystemService $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
    }

    public function restore(string $rootPath, string $restoreDir, string $rollbackDir, callable $log): ?int
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

                if (!$this->backupTarget($targetPath, $relPath, $rollbackDir, $changedFiles)) {
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
            $this->rollback($changedFiles);
            $this->filesystem->removeDirectory($restoreDir);
            $this->filesystem->removeDirectory($rollbackDir);
            $log('Restore failed. Live files have been rolled back where changes were made.');
            return null;
        }

        return $restoredFiles;
    }

    private function backupTarget(string $targetPath, string $relPath, string $rollbackDir, array &$changedFiles): bool
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

    private function rollback(array $changedFiles): void
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
