<?php

namespace DcsStats\Core;

final class SiteFeatureStorage
{
    public function path(): string
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

    public function read(): ?array
    {
        $settingsFile = $this->path();
        if (!file_exists($settingsFile)) {
            return null;
        }

        $content = @file_get_contents($settingsFile);
        if (!$content) {
            return null;
        }

        $saved = json_decode($content, true);
        return is_array($saved) && !empty($saved) ? $saved : null;
    }

    public function write(array $features): bool
    {
        $settingsFile = $this->path();
        $dir = dirname($settingsFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $result = @file_put_contents($settingsFile, json_encode($features, JSON_PRETTY_PRINT));
        if ($result !== false) {
            @chmod($settingsFile, 0600);
        }

        return $result !== false;
    }
}
