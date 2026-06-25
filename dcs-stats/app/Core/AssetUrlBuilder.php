<?php

namespace DcsStats\Core;

final class AssetUrlBuilder
{
    private RequestUrlResolver $urlResolver;

    public function __construct(?RequestUrlResolver $urlResolver = null)
    {
        $this->urlResolver = $urlResolver ?? new RequestUrlResolver();
    }

    public function build(string $path = ''): string
    {
        $url = $this->urlResolver->to($path);
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

        $version = implode('-', array_filter($versionParts)) ?: 'asset';
        $version = preg_replace('/[^A-Za-z0-9._-]/', '-', $version);
        $separator = strpos($url, '?') === false ? '?' : '&';

        return $url . $separator . 'v=' . rawurlencode($version);
    }
}
