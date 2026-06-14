<?php

namespace DcsStats\Services\Admin;

final class ThemePresetService
{
    public function builtInPresets(): array
    {
        return (new ThemePresetCatalog())->builtInPresets();
    }

    public function applyPreset($preset): bool
    {
        if (!is_array($preset)) {
            return false;
        }

        $colorService = new ThemeColorService();
        $colors = $colorService->cleanColors(array_merge($colorService->defaultColors(), $preset['colors'] ?? []));
        $options = $colorService->cleanOptions($preset['options'] ?? []);

        if (file_put_contents(DCS_ROOT_PATH . '/custom_theme.css', $colorService->buildCustomCss($colors, $options)) === false) {
            return false;
        }

        if (isset($preset['chart_colors']) && is_array($preset['chart_colors'])) {
            \saveChartTheme($preset['chart_colors']);
        }

        return true;
    }

    public function saveCurrentPreset(string $presetName): array
    {
        $presetName = trim($presetName);
        if ($presetName === '') {
            return ['success' => false, 'message' => 'Please enter a preset name'];
        }

        if (strlen($presetName) > 60) {
            return ['success' => false, 'message' => 'Preset name must be 60 characters or fewer'];
        }

        $customCss = DCS_ROOT_PATH . '/custom_theme.css';
        $storageService = new ThemePresetStorageService();
        $colorService = new ThemeColorService();
        $presets = $storageService->loadCustomPresets();
        $presets[] = [
            'name' => $presetName,
            'description' => 'Saved custom squadron theme',
            'created_at' => date('c'),
            'colors' => $colorService->loadColorsFromCss($customCss),
            'options' => $colorService->loadOptionsFile($customCss),
            'chart_colors' => \loadChartTheme(),
        ];

        if ($storageService->saveCustomPresets($presets)) {
            return ['success' => true, 'message' => 'Custom theme preset saved successfully'];
        }

        return ['success' => false, 'message' => 'Failed to save custom theme preset'];
    }

    public function deleteCustomPreset(int $presetIndex): array
    {
        $storageService = new ThemePresetStorageService();
        $presets = $storageService->loadCustomPresets();
        if (!isset($presets[$presetIndex])) {
            return ['success' => false, 'message' => 'Custom preset not found'];
        }

        $deletedName = $presets[$presetIndex]['name'] ?? 'Custom preset';
        array_splice($presets, $presetIndex, 1);

        if ($storageService->saveCustomPresets($presets)) {
            return [
                'success' => true,
                'message' => 'Custom theme preset deleted successfully',
                'deleted_name' => $deletedName,
            ];
        }

        return ['success' => false, 'message' => 'Failed to delete custom theme preset'];
    }
}
