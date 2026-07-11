<?php

namespace DcsStats\Core;

final class SiteMetadata
{
    public static function path(): string
    {
        $path = DCS_ROOT_PATH . '/site-config/data/site_metadata.json';
        $dir = dirname($path);

        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        return $path;
    }

    public static function defaults(): array
    {
        return [
            'description' => 'DCS Statistics Dashboard for combat data, pilot statistics, server activity, and squadron insights.',
            'keywords' => 'DCS, DCS World, dashboard, statistics, pilots, squadron, server stats',
            'block_search_engines' => false,
            'show_privacy_link' => false,
            'privacy_notice' => 'This dashboard displays DCS server and pilot statistics provided by the configured DCSServerBot API. Site administrators control what data is shown publicly. No optional install reporting is enabled.',
        ];
    }

    public static function load(): array
    {
        $defaults = self::defaults();
        $path = self::path();

        if (file_exists($path)) {
            $saved = json_decode((string)file_get_contents($path), true);
            if (is_array($saved)) {
                return array_merge($defaults, $saved);
            }
        }

        return $defaults;
    }

    public static function save(array $metadata): bool
    {
        $path = self::path();
        $dir = dirname($path);

        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $clean = [
            'description' => trim((string)($metadata['description'] ?? '')),
            'keywords' => trim((string)($metadata['keywords'] ?? '')),
            'block_search_engines' => !empty($metadata['block_search_engines']),
            'show_privacy_link' => !empty($metadata['show_privacy_link']),
            'privacy_notice' => trim((string)($metadata['privacy_notice'] ?? '')),
        ];

        $result = @file_put_contents($path, json_encode($clean, JSON_PRETTY_PRINT));
        if ($result !== false) {
            @chmod($path, 0600);
        }

        return $result !== false;
    }
}
