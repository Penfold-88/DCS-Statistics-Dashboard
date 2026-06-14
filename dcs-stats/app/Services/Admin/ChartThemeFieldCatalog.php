<?php

namespace DcsStats\Services\Admin;

final class ChartThemeFieldCatalog
{
    public function groups(): array
    {
        return [
            \dcs_t('admin.themes.leaderboard_chart') => [
                ['key' => 'chart_primary_color', 'label' => \dcs_t('admin.themes.chart_primary'), 'title' => \dcs_t('admin.themes.chart_primary_title')],
                ['key' => 'chart_secondary_color', 'label' => \dcs_t('admin.themes.chart_secondary'), 'title' => \dcs_t('admin.themes.chart_secondary_title')],
                ['key' => 'chart_grid_color', 'label' => \dcs_t('admin.themes.grid_lines'), 'title' => \dcs_t('admin.themes.grid_lines_title')],
                ['key' => 'chart_text_color', 'label' => \dcs_t('admin.themes.chart_text'), 'title' => \dcs_t('admin.themes.chart_text_title')],
            ],
            \dcs_t('admin.themes.home_top_pilots') => [
                ['key' => 'home_top_pilots_color', 'label' => \dcs_t('admin.themes.bars'), 'title' => \dcs_t('admin.themes.bars_title')],
                ['key' => 'home_top_pilots_grid_color', 'label' => \dcs_t('admin.themes.grid_lines'), 'title' => \dcs_t('admin.themes.grid_lines_title')],
                ['key' => 'home_top_pilots_text_color', 'label' => \dcs_t('admin.themes.text'), 'title' => \dcs_t('admin.themes.chart_text_title')],
            ],
            \dcs_t('admin.themes.home_combat_stats') => [
                ['key' => 'home_combat_kills_color', 'label' => \dcs_t('home.kills'), 'title' => \dcs_t('admin.themes.combat_kills_title')],
                ['key' => 'home_combat_deaths_color', 'label' => \dcs_t('home.deaths'), 'title' => \dcs_t('admin.themes.combat_deaths_title')],
                ['key' => 'home_combat_text_color', 'label' => \dcs_t('admin.themes.text'), 'title' => \dcs_t('admin.themes.chart_text_title')],
            ],
            \dcs_t('admin.themes.home_top_squadrons') => [
                ['key' => 'home_squadrons_color', 'label' => \dcs_t('admin.themes.bars'), 'title' => \dcs_t('admin.themes.bars_title')],
                ['key' => 'home_squadrons_grid_color', 'label' => \dcs_t('admin.themes.grid_lines'), 'title' => \dcs_t('admin.themes.grid_lines_title')],
                ['key' => 'home_squadrons_text_color', 'label' => \dcs_t('admin.themes.text'), 'title' => \dcs_t('admin.themes.chart_text_title')],
            ],
            \dcs_t('admin.themes.home_player_activity') => [
                ['key' => 'home_activity_color', 'label' => \dcs_t('admin.themes.line_and_fill'), 'title' => \dcs_t('admin.themes.line_fill_title')],
                ['key' => 'home_activity_grid_color', 'label' => \dcs_t('admin.themes.grid_lines'), 'title' => \dcs_t('admin.themes.grid_lines_title')],
                ['key' => 'home_activity_text_color', 'label' => \dcs_t('admin.themes.text'), 'title' => \dcs_t('admin.themes.chart_text_title')],
            ],
        ];
    }
}
