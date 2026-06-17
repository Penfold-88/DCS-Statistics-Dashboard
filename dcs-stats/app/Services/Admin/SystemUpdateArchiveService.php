<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateArchiveService
{
    private AdminFilesystemService $filesystem;

    public function __construct(?AdminFilesystemService $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
    }

    public function extractArchive(string $zipFile, string $upgradeDir, callable $log): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            $log('Failed to open zip archive');
            return null;
        }

        if (!$this->filesystem->archiveHasSafePaths($zip)) {
            $zip->close();
            @unlink($zipFile);
            $log('Update cancelled: downloaded archive contains unsafe file paths.');
            return null;
        }

        $zip->extractTo($upgradeDir);
        $zip->close();
        $log('Extraction complete.');

        $extractedDirs = glob($upgradeDir . '/*', GLOB_ONLYDIR);
        if (empty($extractedDirs)) {
            $log('No extracted directory found');
            return null;
        }

        $newCodeDir = $extractedDirs[0] . '/dcs-stats';
        if (!is_dir($newCodeDir)) {
            $log('dcs-stats directory not found in archive');
            return null;
        }

        return $newCodeDir;
    }
}
