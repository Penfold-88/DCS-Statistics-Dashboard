<?php

namespace DcsStats\Services\Admin;

final class ThemePreviewUrlBuilder
{
    public function build(array $customColors, array $themeOptions): string
    {
        $previewParams = ['preview' => '1'];
        foreach ($customColors as $colorKey => $colorValue) {
            $previewParams[$colorKey] = ltrim((string)$colorValue, '#');
        }
        $previewParams['header_title_gradient_enabled'] = !empty($themeOptions['header_title_gradient_enabled']) ? '1' : '0';
        $previewParams['page_background_gradient_enabled'] = !empty($themeOptions['page_background_gradient_enabled']) ? '1' : '0';

        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $port = (string)($_SERVER['SERVER_PORT'] ?? '');
        $protocol = ($https || $port === '443') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $currentPath = dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/site-config/themes.php'));
        $parentPath = dirname($currentPath);

        return $protocol . $host . ($parentPath === '/' ? '' : $parentPath) . '/index.php?' . http_build_query($previewParams);
    }
}
