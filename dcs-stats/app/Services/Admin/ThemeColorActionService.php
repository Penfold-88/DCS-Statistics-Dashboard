<?php

namespace DcsStats\Services\Admin;

final class ThemeColorActionService
{
    private ThemeColorService $themeColorService;

    public function __construct(?ThemeColorService $themeColorService = null)
    {
        $this->themeColorService = $themeColorService ?? new ThemeColorService();
    }

    public function updateColors(array $post): bool
    {
        $colors = [];
        foreach ($this->themeColorService->defaultColors() as $key => $defaultValue) {
            $colors[$key] = $post[$key] ?? $defaultValue;
        }

        $themeOptions = [
            'header_title_gradient_enabled' => isset($post['header_title_gradient_enabled']),
            'page_background_gradient_enabled' => isset($post['page_background_gradient_enabled']),
        ];

        return file_put_contents(DCS_ROOT_PATH . '/custom_theme.css', $this->themeColorService->buildCustomCss($colors, $themeOptions)) !== false;
    }

    public function updateChartColors(array $post): bool
    {
        $chartTheme = [];
        foreach (\getDefaultChartTheme() as $key => $defaultValue) {
            $chartTheme[$key] = $post[$key] ?? $defaultValue;
        }

        return \saveChartTheme($chartTheme);
    }
}
