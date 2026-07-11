<?php

namespace DcsStats\Services\Admin;

final class SettingsBackupFileStore
{
    public function dataPath(string $fileName): string
    {
        return DCS_ROOT_PATH . '/site-config/data/' . $fileName;
    }

    public function readJsonFile($path, $fallback = null)
    {
        if (!file_exists($path)) {
            return $fallback;
        }

        $data = json_decode((string)file_get_contents($path), true);
        return is_array($data) ? $data : $fallback;
    }

    public function writeJsonFile($path, $data): bool
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $result = @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        if ($result !== false) {
            @chmod($path, 0600);
        }

        return $result !== false;
    }

    public function readTextFile($path): ?string
    {
        return file_exists($path) ? file_get_contents($path) : null;
    }
}
