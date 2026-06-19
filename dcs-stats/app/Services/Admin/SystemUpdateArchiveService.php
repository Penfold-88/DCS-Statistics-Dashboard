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

        if (!$this->hasExpectedStructure($zip)) {
            $zip->close();
            @unlink($zipFile);
            $log('Update cancelled: downloaded archive has an unexpected structure.');
            return null;
        }

        if (!$zip->extractTo($upgradeDir)) {
            $zip->close();
            @unlink($zipFile);
            $log('Update cancelled: archive extraction failed.');
            return null;
        }
        $zip->close();
        $log('Extraction complete.');

        $extractedDirs = glob($upgradeDir . '/*', GLOB_ONLYDIR);
        if (count($extractedDirs) !== 1) {
            $log('No extracted directory found');
            return null;
        }

        $newCodeDir = $extractedDirs[0] . '/dcs-stats';
        if (!is_dir($newCodeDir) || !is_file($newCodeDir . '/app/bootstrap.php')) {
            $log('dcs-stats directory not found in archive');
            return null;
        }

        return $newCodeDir;
    }

    private function hasExpectedStructure(\ZipArchive $zip): bool
    {
        $topLevel = null;
        $bootstrapFound = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = trim((string)$zip->getNameIndex($i), '/');
            if ($name === '') {
                continue;
            }

            $parts = explode('/', $name, 2);
            if ($topLevel === null) {
                $topLevel = $parts[0];
            } elseif ($parts[0] !== $topLevel) {
                return false;
            }

            if ($name === $topLevel . '/dcs-stats/app/bootstrap.php') {
                $bootstrapFound = true;
            }
        }

        return $topLevel !== null && $bootstrapFound;
    }
}
