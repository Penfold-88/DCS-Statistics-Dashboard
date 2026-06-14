<?php

namespace DcsStats\Services\Admin;

final class AdminFilesystemService
{
    public function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    public function ensureDirectory(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }

        return mkdir($dir, 0755, true);
    }

    public function removeDirectory(string $dir): void
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

    public function isSafeArchivePath(string $path): bool
    {
        $path = $this->normalizePath($path);

        return $path !== '' &&
            $path[0] !== '/' &&
            strpos($path, '../') === false &&
            strpos($path, '/..') === false &&
            strpos($path, ':') === false;
    }

    public function archiveHasSafePaths(\ZipArchive $zip): bool
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$this->isSafeArchivePath((string)$name)) {
                return false;
            }
        }

        return true;
    }

    public function shouldPreservePath(string $relPath, array $preserve): bool
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
}
