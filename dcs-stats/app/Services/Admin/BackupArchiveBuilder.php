<?php

namespace DcsStats\Services\Admin;

final class BackupArchiveBuilder
{
    private AdminFilesystemService $filesystem;

    public function __construct(?AdminFilesystemService $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
    }

    public function populate(\ZipArchive $backupZip, callable $log): int
    {
        $this->addConfigurationFiles($backupZip, $log);
        return $this->addProjectFiles($backupZip, $log);
    }

    private function addConfigurationFiles(\ZipArchive $backupZip, callable $log): void
    {
        $log('Backing up configuration files...');
        foreach (BackupFileCatalog::packageConfigurationFiles() as $configFile) {
            $configPath = DCS_ROOT_PATH . '/' . $configFile;
            if (file_exists($configPath)) {
                $backupZip->addFile($configPath, $configFile);
                $log("Config: $configFile");
            }
        }
    }

    private function addProjectFiles(\ZipArchive $backupZip, callable $log): int
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
