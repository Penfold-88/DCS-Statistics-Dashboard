<?php

namespace DcsStats\Core;

final class SiteFeatures
{
    private static ?array $cache = null;

    public static function settingsPath(): string
    {
        $primaryPath = DCS_ROOT_PATH . '/site-config/data/site_settings.json';
        $primaryDir = dirname($primaryPath);

        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }

        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0700, true);
            if (is_dir($primaryDir) && is_writable($primaryDir)) {
                return $primaryPath;
            }
        }

        $altDir = DCS_ROOT_PATH . '/data';
        if (!is_dir($altDir)) {
            @mkdir($altDir, 0700, true);
        }
        if (is_dir($altDir) && is_writable($altDir)) {
            return $altDir . '/site_settings.json';
        }

        $tempDir = sys_get_temp_dir() . '/dcs_stats';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }

        return $tempDir . '/site_settings.json';
    }

    public static function load(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $defaults = self::defaults();
        $settingsFile = self::settingsPath();

        if (file_exists($settingsFile)) {
            $content = @file_get_contents($settingsFile);
            if ($content) {
                $saved = json_decode($content, true);
                if ($saved) {
                    self::$cache = array_merge($defaults, $saved);
                    return self::$cache;
                }
            }
        }

        self::$cache = $defaults;
        return self::$cache;
    }

    public static function save(array $features): bool
    {
        $settingsFile = self::settingsPath();
        $dir = dirname($settingsFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $result = @file_put_contents($settingsFile, json_encode($features, JSON_PRETTY_PRINT));
        if ($result !== false) {
            @chmod($settingsFile, 0600);
            self::$cache = $features;
        }

        return $result !== false;
    }

    public static function isEnabled(string $feature): bool
    {
        $features = self::load();

        return isset($features[$feature]) ? (bool)$features[$feature] : true;
    }

    public static function value(string $feature, $default = '')
    {
        $features = self::load();

        return isset($features[$feature]) ? $features[$feature] : $default;
    }

    public static function serverCardFeatureKey(string $serverName): string
    {
        $slug = strtolower(trim($serverName));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = trim((string)$slug, '_');

        if ($slug === '') {
            $slug = 'unknown_server';
        }

        return 'server_card_' . $slug;
    }

    public static function groups(): array
    {
        return SiteFeatureCatalog::groups();
    }

    public static function dependencies(): array
    {
        return SiteFeatureCatalog::dependencies();
    }

    private static function defaults(): array
    {
        $siteConfig = [];
        $siteConfigFile = DCS_ROOT_PATH . '/site_config.json';
        if (file_exists($siteConfigFile)) {
            $content = @file_get_contents($siteConfigFile);
            if ($content) {
                $siteConfig = json_decode($content, true) ?: [];
            }
        }

        return SiteFeatureCatalog::defaults($siteConfig);
    }
}
