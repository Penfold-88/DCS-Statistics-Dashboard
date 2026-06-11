<?php

namespace DcsStats\Services\Admin;

final class ThemePresetStorageService
{
    public function presetPath(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/theme_presets.json';
    }

    public function loadCustomPresets(): array
    {
        $path = $this->presetPath();
        if (!file_exists($path)) {
            return [];
        }

        $presets = json_decode((string)file_get_contents($path), true);
        return is_array($presets) ? $presets : [];
    }

    public function saveCustomPresets(array $presets): bool
    {
        $path = $this->presetPath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        return @file_put_contents($path, json_encode(array_values($presets), JSON_PRETTY_PRINT)) !== false;
    }

    public function customPresetById($presetId): ?array
    {
        foreach ($this->loadCustomPresets() as $index => $preset) {
            if ((string)$index === (string)$presetId) {
                return $preset;
            }
        }

        return null;
    }
}
