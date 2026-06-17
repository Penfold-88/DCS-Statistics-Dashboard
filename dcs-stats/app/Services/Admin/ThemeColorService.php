<?php

namespace DcsStats\Services\Admin;

final class ThemeColorService
{
    private ThemeColorCatalog $catalog;

    public function __construct(?ThemeColorCatalog $catalog = null)
    {
        $this->catalog = $catalog ?? new ThemeColorCatalog();
    }

    public function defaultColors(): array
    {
        return $this->catalog->defaultColors();
    }

    public function colorGroups(): array
    {
        return $this->catalog->colorGroups();
    }

    public function defaultOptions(): array
    {
        return $this->catalog->defaultOptions();
    }

    public function loadOptionsFromCss(string $content): array
    {
        $options = $this->defaultOptions();
        if (preg_match('/--header_title_gradient_enabled:\s*(0|1);/', $content, $match)) {
            $options['header_title_gradient_enabled'] = $match[1] === '1';
        }
        if (preg_match('/--page_background_gradient_enabled:\s*(0|1);/', $content, $match)) {
            $options['page_background_gradient_enabled'] = $match[1] === '1';
        }

        return $options;
    }

    public function loadColorsFromCss(string $customCssPath): array
    {
        $colors = $this->defaultColors();
        if (!file_exists($customCssPath)) {
            return $colors;
        }

        $content = file_get_contents($customCssPath);
        preg_match_all('/--([a-z_]+):\s*(#[0-9a-fA-F]{6});/', $content, $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
            foreach ($matches[1] as $i => $varName) {
                if (isset($colors[$varName])) {
                    $colors[$varName] = $matches[2][$i];
                }
            }
        }

        return $colors;
    }

    public function loadOptionsFile(string $customCssPath): array
    {
        if (!file_exists($customCssPath)) {
            return $this->defaultOptions();
        }

        return $this->loadOptionsFromCss((string)file_get_contents($customCssPath));
    }

    public function cleanColors($colors): array
    {
        $clean = [];
        foreach ($this->defaultColors() as $key => $defaultValue) {
            $value = $colors[$key] ?? $defaultValue;
            $clean[$key] = is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : $defaultValue;
        }

        return $clean;
    }

    public function buildCustomCss(array $colors, array $options = []): string
    {
        $options = array_merge($this->defaultOptions(), $options);
        $cssVars = ":root {\n";
        foreach ($colors as $key => $value) {
            if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                $cssVars .= "    --{$key}: {$value};\n";
            }
        }
        $gradientEnabled = !empty($options['header_title_gradient_enabled']);
        $cssVars .= "    --header_title_gradient_enabled: " . ($gradientEnabled ? "1" : "0") . ";\n";
        $pageGradientEnabled = !empty($options['page_background_gradient_enabled']);
        $cssVars .= "    --page_background_gradient_enabled: " . ($pageGradientEnabled ? "1" : "0") . ";\n";
        if ($gradientEnabled) {
            $cssVars .= "    --header_title_background: linear-gradient(135deg, var(--header_text_color) 0%, var(--header_title_gradient_color) 100%);\n";
            $cssVars .= "    --header_title_fill: transparent;\n";
        } else {
            $cssVars .= "    --header_title_background: none;\n";
            $cssVars .= "    --header_title_fill: var(--header_text_color);\n";
        }
        if ($pageGradientEnabled) {
            $cssVars .= "    --page_background_css: radial-gradient(circle at top left, color-mix(in srgb, var(--background_gradient_color) 36%, transparent) 0%, transparent 34%), linear-gradient(135deg, var(--background_color) 0%, var(--background_gradient_color) 100%);\n";
        } else {
            $cssVars .= "    --page_background_css: var(--background_color);\n";
        }
        $cssVars .= "}\n\n";

        $cssVars .= $this->customCssTemplate();

        return $cssVars;
    }

    private function customCssTemplate(): string
    {
        $templatePath = DCS_APP_PATH . '/Templates/themes/custom_theme.css';
        if (!is_file($templatePath)) {
            return '';
        }

        return (string)file_get_contents($templatePath);
    }

    public function cleanOptions($options): array
    {
        return [
            'header_title_gradient_enabled' => !empty($options['header_title_gradient_enabled']),
            'page_background_gradient_enabled' => !empty($options['page_background_gradient_enabled']),
        ];
    }
}
