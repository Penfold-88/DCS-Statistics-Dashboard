<?php

namespace DcsStats\Core;

final class ApiConfigPathResolver
{
    public function writablePath(?string $preferredFile = null): string
    {
        if ($preferredFile === null) {
            $legacyFile = DCS_ROOT_PATH . '/api_config.json';
            if (file_exists($legacyFile)) {
                return $legacyFile;
            }
            $preferredFile = DCS_ROOT_PATH . '/site-config/data/api_config.json';
        }

        if (file_exists($preferredFile) && is_writable($preferredFile)) {
            return $preferredFile;
        }

        $dir = dirname($preferredFile);
        if (is_dir($dir) && is_writable($dir)) {
            return $preferredFile;
        }

        $dataDir = DCS_ROOT_PATH . '/site-config/data/';
        if (is_dir($dataDir) && is_writable($dataDir)) {
            return $dataDir . 'api_config.json';
        }

        $dataDir = DCS_ROOT_PATH . '/data/';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0700, true);
            @chmod($dataDir, 0700);
        }
        if (is_writable($dataDir)) {
            return $dataDir . 'api_config.json';
        }

        if (is_writable(DCS_ROOT_PATH)) {
            return DCS_ROOT_PATH . '/api_config.json';
        }

        $tempDir = sys_get_temp_dir() . '/dcs_stats/';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }

        return $tempDir . 'api_config.json';
    }
}
