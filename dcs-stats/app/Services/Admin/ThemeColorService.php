<?php

namespace DcsStats\Services\Admin;

final class ThemeColorService
{
    public function defaultColors(): array
    {
        return [
            'primary_color' => '#1a1a1a',
            'secondary_color' => '#2a2a2a',
            'background_color' => '#121212',
            'background_gradient_color' => '#1f2b22',
            'surface_color' => '#2c2c2c',
            'surface_dark_color' => '#1e1e1e',
            'card_color' => '#2c2c2c',
            'card_alt_color' => '#1e1e1e',
            'card_heading_color' => '#4CAF50',
            'card_text_color' => '#ffffff',
            'card_muted_text_color' => '#cccccc',
            'text_color' => '#ffffff',
            'muted_text_color' => '#cccccc',
            'heading_color' => '#4CAF50',
            'link_color' => '#4a9eff',
            'accent_color' => '#4CAF50',
            'accent_hover_color' => '#7ad77d',
            'border_color' => '#556b2f',
            'nav_background_color' => '#1a1a1a',
            'nav_text_color' => '#ffffff',
            'nav_hover_color' => '#4CAF50',
            'header_text_color' => '#ffffff',
            'header_title_gradient_color' => '#4CAF50',
            'header_subtitle_color' => '#e0e0e0',
            'footer_background_color' => '#2a2a2a',
            'footer_text_color' => '#e0e0e0',
            'success_color' => '#4CAF50',
            'warning_color' => '#ff9800',
            'danger_color' => '#f44336',
            'info_color' => '#2196F3',
            'table_header_color' => '#1e1e1e',
            'table_header_text_color' => '#4CAF50',
            'table_row_color' => '#2c2c2c',
            'table_text_color' => '#ffffff',
            'table_player_name_color' => '#e0e0e0',
            'table_hover_color' => '#3a3a3a',
        ];
    }

    public function colorGroups(): array
    {
        return [
            \dcs_t('admin.themes.group_page_text') => [
                'background_color' => \dcs_t('admin.themes.page_background'),
                'background_gradient_color' => \dcs_t('admin.themes.page_gradient_end'),
                'text_color' => \dcs_t('admin.themes.main_text'),
                'muted_text_color' => \dcs_t('admin.themes.muted_text'),
                'heading_color' => \dcs_t('admin.themes.headings'),
                'link_color' => \dcs_t('admin.themes.links'),
            ],
            \dcs_t('admin.themes.group_brand') => [
                'accent_color' => \dcs_t('admin.themes.main_accent'),
                'accent_hover_color' => \dcs_t('admin.themes.accent_hover'),
                'border_color' => \dcs_t('admin.themes.borders'),
                'success_color' => \dcs_t('admin.themes.success'),
                'warning_color' => \dcs_t('admin.themes.warning'),
                'danger_color' => \dcs_t('admin.themes.danger'),
                'info_color' => \dcs_t('admin.themes.info'),
            ],
            \dcs_t('admin.themes.group_panels') => [
                'surface_color' => \dcs_t('admin.themes.panel_top'),
                'surface_dark_color' => \dcs_t('admin.themes.panel_bottom'),
                'card_color' => \dcs_t('admin.themes.card_top'),
                'card_alt_color' => \dcs_t('admin.themes.card_bottom'),
                'card_heading_color' => \dcs_t('admin.themes.card_headings'),
                'card_text_color' => \dcs_t('admin.themes.card_text'),
                'card_muted_text_color' => \dcs_t('admin.themes.card_muted_text'),
                'secondary_color' => \dcs_t('admin.themes.secondary_surface'),
            ],
            \dcs_t('admin.themes.group_header_nav') => [
                'primary_color' => \dcs_t('admin.themes.primary_background'),
                'nav_background_color' => \dcs_t('admin.themes.nav_background'),
                'nav_text_color' => \dcs_t('admin.themes.nav_text'),
                'nav_hover_color' => \dcs_t('admin.themes.nav_hover'),
                'header_text_color' => \dcs_t('admin.themes.header_title'),
                'header_title_gradient_color' => \dcs_t('admin.themes.header_gradient_end'),
                'header_subtitle_color' => \dcs_t('admin.themes.header_subtitle'),
            ],
            \dcs_t('admin.themes.group_footer') => [
                'footer_background_color' => \dcs_t('admin.themes.footer_background'),
                'footer_text_color' => \dcs_t('admin.themes.footer_text'),
            ],
            \dcs_t('admin.themes.group_tables') => [
                'table_header_color' => \dcs_t('admin.themes.table_header'),
                'table_header_text_color' => \dcs_t('admin.themes.table_header_text'),
                'table_row_color' => \dcs_t('admin.themes.table_row'),
                'table_text_color' => \dcs_t('admin.themes.table_text'),
                'table_player_name_color' => \dcs_t('admin.themes.table_player_names'),
                'table_hover_color' => \dcs_t('admin.themes.table_hover'),
            ],
        ];
    }

    public function defaultOptions(): array
    {
        return [
            'header_title_gradient_enabled' => false,
            'page_background_gradient_enabled' => false,
        ];
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
