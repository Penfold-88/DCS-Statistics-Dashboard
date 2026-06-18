<?php

namespace DcsStats\Services\Admin;

final class ThemeSettingsBackupService
{
    private ThemeColorService $colorService;
    private ThemeAssetService $assetService;

    public function __construct(?ThemeColorService $colorService = null, ?ThemeAssetService $assetService = null)
    {
        $this->colorService = $colorService ?? new ThemeColorService();
        $this->assetService = $assetService ?? new ThemeAssetService();
    }

    public function buildBackup(string $menuConfigFile): array
    {
        $customCss = DCS_ROOT_PATH . '/custom_theme.css';

        return [
            'type' => 'dcs_statistics_dashboard_theme_settings',
            'version' => 1,
            'created_at' => date('c'),
            'theme_colors' => $this->colorService->loadColorsFromCss($customCss),
            'theme_options' => $this->colorService->loadOptionsFile($customCss),
            'chart_colors' => \loadChartTheme(),
            'header_image' => $this->assetService->loadHeaderImageSettings(),
            'menu' => file_exists($menuConfigFile) ? json_decode((string)file_get_contents($menuConfigFile), true) : null,
        ];
    }

    public function importBackup($backup, string $menuConfigFile): bool
    {
        if (!is_array($backup) || ($backup['type'] ?? '') !== 'dcs_statistics_dashboard_theme_settings') {
            return false;
        }

        $colors = $this->colorService->cleanColors($backup['theme_colors'] ?? []);
        $options = $this->colorService->cleanOptions($backup['theme_options'] ?? []);
        if (file_put_contents(DCS_ROOT_PATH . '/custom_theme.css', $this->colorService->buildCustomCss($colors, $options)) === false) {
            return false;
        }

        if (isset($backup['chart_colors']) && is_array($backup['chart_colors'])) {
            \saveChartTheme($backup['chart_colors']);
        }

        if (isset($backup['header_image']) && is_array($backup['header_image'])) {
            $this->assetService->saveHeaderImageSettings($backup['header_image']);
        }

        if (isset($backup['menu']) && is_array($backup['menu'])) {
            file_put_contents($menuConfigFile, json_encode($backup['menu'], JSON_PRETTY_PRINT));
        }

        return true;
    }
}
