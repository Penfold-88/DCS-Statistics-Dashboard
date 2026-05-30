<?php
/**
 * Public site metadata settings.
 */

function getSiteMetadataPath() {
    $path = __DIR__ . '/site-config/data/site_metadata.json';
    $dir = dirname($path);

    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    return $path;
}

function getDefaultSiteMetadata() {
    return [
        'description' => 'DCS Statistics Dashboard for combat data, pilot statistics, server activity, and squadron insights.',
        'keywords' => 'DCS, DCS World, dashboard, statistics, pilots, squadron, server stats',
        'block_search_engines' => false,
        'show_privacy_link' => false,
        'privacy_notice' => 'This dashboard displays DCS server and pilot statistics provided by the configured DCSServerBot API. Site administrators control what data is shown publicly. No optional install reporting is enabled.'
    ];
}

function loadSiteMetadata() {
    $defaults = getDefaultSiteMetadata();
    $path = getSiteMetadataPath();

    if (file_exists($path)) {
        $saved = json_decode(file_get_contents($path), true);
        if (is_array($saved)) {
            return array_merge($defaults, $saved);
        }
    }

    return $defaults;
}

function saveSiteMetadata($metadata) {
    $path = getSiteMetadataPath();
    $dir = dirname($path);

    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    $clean = [
        'description' => trim((string)($metadata['description'] ?? '')),
        'keywords' => trim((string)($metadata['keywords'] ?? '')),
        'block_search_engines' => !empty($metadata['block_search_engines']),
        'show_privacy_link' => !empty($metadata['show_privacy_link']),
        'privacy_notice' => trim((string)($metadata['privacy_notice'] ?? ''))
    ];

    $result = @file_put_contents($path, json_encode($clean, JSON_PRETTY_PRINT));
    if ($result !== false) {
        @chmod($path, 0600);
    }

    return $result !== false;
}
