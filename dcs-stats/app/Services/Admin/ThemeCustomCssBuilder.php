<?php

namespace DcsStats\Services\Admin;

final class ThemeCustomCssBuilder
{
    private ThemeColorCatalog $catalog;

    public function __construct(?ThemeColorCatalog $catalog = null)
    {
        $this->catalog = $catalog ?? new ThemeColorCatalog();
    }

    public function build(array $colors, array $options = []): string
    {
        $options = array_merge($this->catalog->defaultOptions(), $options);
        $css = ":root {\n";
        foreach ($colors as $key => $value) {
            if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                $css .= "    --{$key}: {$value};\n";
            }
        }

        $gradientEnabled = !empty($options['header_title_gradient_enabled']);
        $pageGradientEnabled = !empty($options['page_background_gradient_enabled']);
        $css .= "    --header_title_gradient_enabled: " . ($gradientEnabled ? '1' : '0') . ";\n";
        $css .= "    --page_background_gradient_enabled: " . ($pageGradientEnabled ? '1' : '0') . ";\n";
        if ($gradientEnabled) {
            $css .= "    --header_title_background: linear-gradient(135deg, var(--header_text_color) 0%, var(--header_title_gradient_color) 100%);\n";
            $css .= "    --header_title_fill: transparent;\n";
        } else {
            $css .= "    --header_title_background: none;\n";
            $css .= "    --header_title_fill: var(--header_text_color);\n";
        }
        if ($pageGradientEnabled) {
            $css .= "    --page_background_css: radial-gradient(circle at top left, color-mix(in srgb, var(--background_gradient_color) 36%, transparent) 0%, transparent 34%), linear-gradient(135deg, var(--background_color) 0%, var(--background_gradient_color) 100%);\n";
        } else {
            $css .= "    --page_background_css: var(--background_color);\n";
        }
        $css .= "}\n\n";

        $templatePath = DCS_APP_PATH . '/Templates/themes/custom_theme.css';
        return $css . (is_file($templatePath) ? (string)file_get_contents($templatePath) : '');
    }
}
