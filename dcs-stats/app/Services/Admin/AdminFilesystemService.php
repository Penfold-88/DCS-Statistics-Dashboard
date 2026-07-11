<?php

namespace DcsStats\Services\Admin;

final class AdminFilesystemService
{
    public function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    public function ensureDirectory(string $dir, int $permissions = 0755): bool
    {
        if (is_dir($dir)) {
            @chmod($dir, $permissions);
            return true;
        }

        return mkdir($dir, $permissions, true);
    }

    public function removeDirectory(string $dir): void
    {
        if (is_link($dir)) {
            unlink($dir);
            return;
        }

        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_link($path)) {
                unlink($path);
            } elseif (is_dir($path)) {
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
        $totalUncompressedSize = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$this->isSafeArchivePath((string)$name)) {
                return false;
            }

            $stat = $zip->statIndex($i);
            if (!is_array($stat)) {
                return false;
            }

            $totalUncompressedSize += (int)($stat['size'] ?? 0);
            if ($totalUncompressedSize > 268435456) {
                return false;
            }

            $attributes = $zip->getExternalAttributesIndex($i, $opsys, $externalAttributes);
            if ($attributes && (($externalAttributes >> 16) & 0170000) === 0120000) {
                return false;
            }
        }

        return $zip->numFiles <= 20000;
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
