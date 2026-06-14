<?php

namespace DcsStats\Services\Admin;

final class ThemeAssetService
{
    public function defaultHeaderImageSettings(): array
    {
        return [
            'image' => 'dcs-header-image.jpg',
            'position_x' => 50,
            'position_y' => 50,
            'branding_mode' => 'text',
            'logo' => '',
            'logo_height' => 72,
            'background_image' => '',
            'background_position_x' => 50,
            'background_position_y' => 50,
            'background_zoom' => 125,
        ];
    }

    public function headerImageSettingsPath(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/header_image.json';
    }

    public function isAllowedImageFile($filePath, $extension = null): bool
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

        if (function_exists('getimagesize') && getimagesize($filePath) === false) {
            return false;
        }

        return true;
    }

    public function normalizeImagePath($path, bool $allowDefaultHeader = false): string
    {
        $path = str_replace('\\', '/', trim((string)$path));
        $path = ltrim($path, '/');

        if ($path === '') {
            return '';
        }

        if ($allowDefaultHeader && $path === 'dcs-header-image.jpg' && $this->isAllowedImageFile(DCS_ROOT_PATH . '/dcs-header-image.jpg', 'jpg')) {
            return $path;
        }

        if (!preg_match('#^uploads/[A-Za-z0-9._-]+\.(jpe?g|png|webp)$#i', $path, $matches)) {
            return '';
        }

        $root = realpath(DCS_ROOT_PATH);
        $uploadsDir = realpath(DCS_ROOT_PATH . '/uploads');
        $realPath = realpath(DCS_ROOT_PATH . '/' . $path);
        if ($root === false || $uploadsDir === false || $realPath === false || !is_file($realPath)) {
            return '';
        }

        $uploadsPrefix = rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strpos($realPath, $uploadsPrefix) !== 0) {
            return '';
        }

        if (!$this->isAllowedImageFile($realPath, $matches[1] ?? null)) {
            return '';
        }

        return $path;
    }

    public function cssImageUrl($path): string
    {
        return str_replace(["\\", "'", ")", "("], ['/', "\\'", "\\)", "\\("], $this->normalizeImagePath($path, true));
    }

    public function loadHeaderImageSettings(): array
    {
        $settings = $this->defaultHeaderImageSettings();
        $path = $this->headerImageSettingsPath();
        if (file_exists($path)) {
            $saved = json_decode((string)file_get_contents($path), true);
            if (is_array($saved)) {
                $settings = array_merge($settings, array_intersect_key($saved, $settings));
            }
        }

        return (new HeaderImageSettingsSanitizer($this))->clean($settings);
    }

    public function saveHeaderImageSettings(array $settings): bool
    {
        $dataDir = DCS_ROOT_PATH . '/site-config/data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        $settings = (new HeaderImageSettingsSanitizer($this))->clean($settings);

        $css = (new HeaderImageCssBuilder($this))->build($settings);

        return file_put_contents($this->headerImageSettingsPath(), json_encode($settings, JSON_PRETTY_PRINT)) !== false
            && file_put_contents(DCS_ROOT_PATH . '/header_custom.css', $css) !== false;
    }
}
