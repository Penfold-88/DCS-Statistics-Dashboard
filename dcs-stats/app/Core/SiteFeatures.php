<?php

namespace DcsStats\Core;

final class SiteFeatures
{
    private static ?array $cache = null;

    public static function settingsPath(): string
    {
        return (new SiteFeatureStorage())->path();
    }

    public static function load(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $defaults = (new SiteFeatureDefaultsProvider())->defaults();
        $saved = (new SiteFeatureStorage())->read();
        if ($saved !== null) {
            self::$cache = array_merge($defaults, $saved);
            return self::$cache;
        }

        self::$cache = $defaults;
        return self::$cache;
    }

    public static function save(array $features): bool
    {
        $saved = (new SiteFeatureStorage())->write($features);
        if ($saved) {
            self::$cache = $features;
        }

        return $saved;
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

}
