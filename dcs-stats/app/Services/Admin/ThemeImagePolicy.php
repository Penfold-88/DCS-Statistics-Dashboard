<?php

namespace DcsStats\Services\Admin;

final class ThemeImagePolicy
{
    public function isAllowedFile($filePath, $extension = null): bool
    {
        if (!is_file($filePath)) {
            return false;
        }

        $extension = strtolower((string)($extension ?: pathinfo($filePath, PATHINFO_EXTENSION)));
        $allowedTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];
        if (!isset($allowedTypes[$extension])) {
            return false;
        }

        $mimeType = function_exists('mime_content_type') ? mime_content_type($filePath) : null;
        if (!is_string($mimeType) || $mimeType !== $allowedTypes[$extension]) {
            return false;
        }

        return !function_exists('getimagesize') || getimagesize($filePath) !== false;
    }

    public function normalizePath($path, bool $allowDefaultHeader = false): string
    {
        $path = ltrim(str_replace('\\', '/', trim((string)$path)), '/');
        if ($path === '') {
            return '';
        }

        if (
            $allowDefaultHeader
            && $path === 'dcs-header-image.jpg'
            && $this->isAllowedFile(DCS_ROOT_PATH . '/dcs-header-image.jpg', 'jpg')
        ) {
            return $path;
        }

        if (!preg_match('#^uploads/[A-Za-z0-9._-]+\.(jpe?g|png|webp)$#i', $path, $matches)) {
            return '';
        }

        $uploadsDir = realpath(DCS_ROOT_PATH . '/uploads');
        $realPath = realpath(DCS_ROOT_PATH . '/' . $path);
        if ($uploadsDir === false || $realPath === false || !is_file($realPath)) {
            return '';
        }

        $uploadsPrefix = rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strpos($realPath, $uploadsPrefix) !== 0 || !$this->isAllowedFile($realPath, $matches[1] ?? null)) {
            return '';
        }

        return $path;
    }

    public function cssUrl($path): string
    {
        return str_replace(
            ["\\", "'", ")", "("],
            ['/', "\\'", "\\)", "\\("],
            $this->normalizePath($path, true)
        );
    }
}
