<?php

namespace DcsStats\Core;

final class Url
{
    public static function basePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName === '') {
            return '';
        }

        $scriptPath = dirname($scriptName);

        if ($scriptPath === '/' || $scriptPath === '\\' || $scriptPath === '.') {
            return '';
        }

        return rtrim($scriptPath, '/\\');
    }

    public static function baseUrl(): string
    {
        $serverPort = $_SERVER['SERVER_PORT'] ?? null;
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $serverPort == 443) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $protocol . $host . self::basePath();
    }

    public static function to(string $path = ''): string
    {
        if ($path === '') {
            return BASE_PATH;
        }

        $path = ltrim($path, '/');
        if (BASE_PATH === '') {
            return '/' . $path;
        }

        return BASE_PATH . '/' . $path;
    }

    public static function asset(string $path = ''): string
    {
        $url = self::to($path);
        $path = ltrim($path, '/');
        $assetPath = DCS_ROOT_PATH . '/' . $path;
        $versionParts = [];
        $metaFile = DCS_ROOT_PATH . '/.version_meta.json';

        if (file_exists($metaFile)) {
            $meta = json_decode((string)file_get_contents($metaFile), true);
            if (is_array($meta)) {
                if (!empty($meta['version'])) {
                    $versionParts[] = (string)$meta['version'];
                }
                if (!empty($meta['commit_sha'])) {
                    $versionParts[] = substr((string)$meta['commit_sha'], 0, 12);
                }
            }
        }

        if (defined('ADMIN_PANEL_VERSION')) {
            array_unshift($versionParts, ADMIN_PANEL_VERSION);
        }

        if (file_exists($assetPath)) {
            $versionParts[] = (string)filemtime($assetPath);
        }

        $version = implode('-', array_filter($versionParts));
        if ($version === '') {
            $version = 'asset';
        }

        $version = preg_replace('/[^A-Za-z0-9._-]/', '-', $version);
        $separator = (strpos($url, '?') === false) ? '?' : '&';

        return $url . $separator . 'v=' . rawurlencode($version);
    }

    public static function isDemoMode(): bool
    {
        return file_exists(DCS_ROOT_PATH . '/.demo') || file_exists(dirname(DCS_ROOT_PATH) . '/.demo');
    }

    public static function absolute(string $path = ''): string
    {
        if ($path === '') {
            return BASE_URL;
        }

        return BASE_URL . '/' . ltrim($path, '/');
    }

    public static function jsConfig(): string
    {
        return json_encode([
            'basePath' => BASE_PATH,
            'baseUrl' => BASE_URL,
        ]);
    }
}
