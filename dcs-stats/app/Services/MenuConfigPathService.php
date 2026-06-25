<?php

namespace DcsStats\Services;

final class MenuConfigPathService
{
    public function configPath(): string
    {
        $primaryPath = DCS_ROOT_PATH . '/site-config/data/menu_config.json';
        $primaryDir = dirname($primaryPath);

        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }

        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0700, true);
            @chmod($primaryDir, 0700);
            if (is_dir($primaryDir) && is_writable($primaryDir)) {
                return $primaryPath;
            }
        }

        $altPath = DCS_ROOT_PATH . '/menu_config.json';
        if (is_writable(dirname($altPath))) {
            return $altPath;
        }

        $tempDir = sys_get_temp_dir() . '/dcs_stats';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }

        return $tempDir . '/menu_config.json';
    }
}
