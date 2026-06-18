<?php

namespace DcsStats\Services\Admin;

final class ThemePresetService
{
    private ThemePresetCatalog $catalog;
    private ThemeColorService $colorService;
    private ThemePresetStorageService $storageService;

    public function __construct(
        ?ThemePresetCatalog $catalog = null,
        ?ThemeColorService $colorService = null,
        ?ThemePresetStorageService $storageService = null
    ) {
        $this->colorService = $colorService ?? new ThemeColorService();
        $this->catalog = $catalog ?? new ThemePresetCatalog($this->colorService);
        $this->storageService = $storageService ?? new ThemePresetStorageService();
    }

    public function builtInPresets(): array
    {
        return $this->catalog->builtInPresets();
    }

    public function applyPreset($preset): bool
    {
        if (!is_array($preset)) {
            return false;
        }

        $colors = $this->colorService->cleanColors(array_merge($this->colorService->defaultColors(), $preset['colors'] ?? []));
        $options = $this->colorService->cleanOptions($preset['options'] ?? []);

        if (file_put_contents(DCS_ROOT_PATH . '/custom_theme.css', $this->colorService->buildCustomCss($colors, $options)) === false) {
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
        $presets = $this->storageService->loadCustomPresets();
        $presets[] = [
            'name' => $presetName,
            'description' => 'Saved custom squadron theme',
            'created_at' => date('c'),
            'colors' => $this->colorService->loadColorsFromCss($customCss),
            'options' => $this->colorService->loadOptionsFile($customCss),
            'chart_colors' => \loadChartTheme(),
        ];

        if ($this->storageService->saveCustomPresets($presets)) {
            return ['success' => true, 'message' => 'Custom theme preset saved successfully'];
        }

        return ['success' => false, 'message' => 'Failed to save custom theme preset'];
    }

    public function deleteCustomPreset(int $presetIndex): array
    {
        $presets = $this->storageService->loadCustomPresets();
        if (!isset($presets[$presetIndex])) {
            return ['success' => false, 'message' => 'Custom preset not found'];
        }

        $deletedName = $presets[$presetIndex]['name'] ?? 'Custom preset';
        array_splice($presets, $presetIndex, 1);

        if ($this->storageService->saveCustomPresets($presets)) {
            return [
                'success' => true,
                'message' => 'Custom theme preset deleted successfully',
                'deleted_name' => $deletedName,
            ];
        }

        return ['success' => false, 'message' => 'Failed to delete custom theme preset'];
    }
}
