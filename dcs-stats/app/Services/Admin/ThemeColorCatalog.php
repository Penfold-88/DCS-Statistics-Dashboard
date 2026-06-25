<?php

namespace DcsStats\Services\Admin;

final class ThemeColorCatalog
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
}
