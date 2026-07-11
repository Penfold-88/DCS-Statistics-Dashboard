<?php

namespace DcsStats\Services\Admin;

final class ThemePresetCatalog
{
    private ThemeColorService $colorService;

    public function __construct(?ThemeColorService $colorService = null)
    {
        $this->colorService = $colorService ?? new ThemeColorService();
    }

    public function builtInPresets(): array
    {
        $defaults = $this->colorService->defaultColors();

        return [
            'carrier_night' => [
                'name' => \dcs_t('admin.themes.preset_carrier_night'),
                'description' => \dcs_t('admin.themes.preset_carrier_night_desc'),
                'colors' => $defaults,
                'options' => ['header_title_gradient_enabled' => false],
                'chart_colors' => \getDefaultChartTheme()
            ],
            'blue_angels' => [
                'name' => \dcs_t('admin.themes.preset_blue_angels'),
                'description' => \dcs_t('admin.themes.preset_blue_angels_desc'),
                'colors' => array_merge($defaults, [
                    'primary_color' => '#071426',
                    'secondary_color' => '#102b4e',
                    'background_color' => '#06111f',
                    'surface_color' => '#12355d',
                    'surface_dark_color' => '#081b31',
                    'card_color' => '#12355d',
                    'card_alt_color' => '#081b31',
                    'card_heading_color' => '#f4c542',
                    'card_text_color' => '#ffffff',
                    'card_muted_text_color' => '#c9d8ee',
                    'text_color' => '#f4f8ff',
                    'muted_text_color' => '#b8c7da',
                    'heading_color' => '#f4c542',
                    'link_color' => '#73b7ff',
                    'accent_color' => '#f4c542',
                    'accent_hover_color' => '#ffe27a',
                    'border_color' => '#315b89',
                    'nav_background_color' => '#071426',
                    'nav_text_color' => '#f4f8ff',
                    'nav_hover_color' => '#f4c542',
                    'header_text_color' => '#ffffff',
                    'header_title_gradient_color' => '#f4c542',
                    'header_subtitle_color' => '#c9d8ee',
                    'footer_background_color' => '#081b31',
                    'footer_text_color' => '#dce8f7',
                    'table_header_color' => '#081b31',
                    'table_header_text_color' => '#f4c542',
                    'table_row_color' => '#102b4e',
                    'table_text_color' => '#f4f8ff',
                    'table_player_name_color' => '#ffe27a',
                    'table_hover_color' => '#173e6d'
                ]),
                'options' => ['header_title_gradient_enabled' => true],
                'chart_colors' => array_merge(\getDefaultChartTheme(), [
                    'chart_primary_color' => '#f4c542',
                    'chart_secondary_color' => '#73b7ff',
                    'chart_grid_color' => '#315b89',
                    'chart_text_color' => '#f4f8ff',
                    'home_top_pilots_color' => '#f4c542',
                    'home_top_pilots_grid_color' => '#315b89',
                    'home_top_pilots_text_color' => '#f4f8ff',
                    'home_activity_color' => '#73b7ff',
                    'home_activity_grid_color' => '#315b89',
                    'home_activity_text_color' => '#f4f8ff'
                ])
            ],
            'red_flag' => [
                'name' => \dcs_t('admin.themes.preset_red_flag'),
                'description' => \dcs_t('admin.themes.preset_red_flag_desc'),
                'colors' => array_merge($defaults, [
                    'primary_color' => '#171717',
                    'secondary_color' => '#292323',
                    'background_color' => '#101010',
                    'surface_color' => '#342525',
                    'surface_dark_color' => '#1d1818',
                    'card_color' => '#342525',
                    'card_alt_color' => '#1d1818',
                    'card_heading_color' => '#ff4d4d',
                    'card_text_color' => '#fff3ef',
                    'card_muted_text_color' => '#d8c8c0',
                    'text_color' => '#fff3ef',
                    'muted_text_color' => '#c7b8b0',
                    'heading_color' => '#ff4d4d',
                    'link_color' => '#ffb454',
                    'accent_color' => '#d92828',
                    'accent_hover_color' => '#ff6b6b',
                    'border_color' => '#744040',
                    'nav_background_color' => '#171717',
                    'nav_text_color' => '#fff3ef',
                    'nav_hover_color' => '#ff4d4d',
                    'header_text_color' => '#fff3ef',
                    'header_title_gradient_color' => '#ffb454',
                    'header_subtitle_color' => '#d8c8c0',
                    'footer_background_color' => '#1d1818',
                    'footer_text_color' => '#d8c8c0',
                    'table_header_color' => '#1d1818',
                    'table_header_text_color' => '#ff4d4d',
                    'table_row_color' => '#292323',
                    'table_text_color' => '#fff3ef',
                    'table_player_name_color' => '#ffb454',
                    'table_hover_color' => '#3f2d2d'
                ]),
                'options' => ['header_title_gradient_enabled' => true],
                'chart_colors' => array_merge(\getDefaultChartTheme(), [
                    'chart_primary_color' => '#d92828',
                    'chart_secondary_color' => '#ffb454',
                    'chart_grid_color' => '#744040',
                    'chart_text_color' => '#fff3ef',
                    'home_combat_kills_color' => '#d92828',
                    'home_combat_deaths_color' => '#ffb454',
                    'home_combat_text_color' => '#fff3ef',
                    'home_activity_color' => '#ff4d4d',
                    'home_activity_grid_color' => '#744040',
                    'home_activity_text_color' => '#fff3ef'
                ])
            ],
            'arctic_ops' => [
                'name' => \dcs_t('admin.themes.preset_arctic_ops'),
                'description' => \dcs_t('admin.themes.preset_arctic_ops_desc'),
                'colors' => array_merge($defaults, [
                    'primary_color' => '#17202a',
                    'secondary_color' => '#243241',
                    'background_color' => '#101820',
                    'surface_color' => '#2d3d4f',
                    'surface_dark_color' => '#1a2632',
                    'card_color' => '#2d3d4f',
                    'card_alt_color' => '#1a2632',
                    'card_heading_color' => '#79d7ff',
                    'card_text_color' => '#f4fbff',
                    'card_muted_text_color' => '#c3d5e0',
                    'text_color' => '#f4fbff',
                    'muted_text_color' => '#b7c8d4',
                    'heading_color' => '#79d7ff',
                    'link_color' => '#93c5fd',
                    'accent_color' => '#38bdf8',
                    'accent_hover_color' => '#8bdfff',
                    'border_color' => '#466075',
                    'nav_background_color' => '#17202a',
                    'nav_text_color' => '#f4fbff',
                    'nav_hover_color' => '#79d7ff',
                    'header_text_color' => '#ffffff',
                    'header_title_gradient_color' => '#79d7ff',
                    'header_subtitle_color' => '#c3d5e0',
                    'footer_background_color' => '#1a2632',
                    'footer_text_color' => '#c3d5e0',
                    'table_header_color' => '#1a2632',
                    'table_header_text_color' => '#79d7ff',
                    'table_row_color' => '#243241',
                    'table_text_color' => '#f4fbff',
                    'table_player_name_color' => '#93c5fd',
                    'table_hover_color' => '#33475c'
                ]),
                'options' => ['header_title_gradient_enabled' => true],
                'chart_colors' => array_merge(\getDefaultChartTheme(), [
                    'chart_primary_color' => '#38bdf8',
                    'chart_secondary_color' => '#93c5fd',
                    'chart_grid_color' => '#466075',
                    'chart_text_color' => '#f4fbff',
                    'home_top_pilots_color' => '#38bdf8',
                    'home_squadrons_color' => '#93c5fd',
                    'home_activity_color' => '#79d7ff',
                    'home_activity_grid_color' => '#466075',
                    'home_activity_text_color' => '#f4fbff'
                ])
            ]
        ];
    }
}
