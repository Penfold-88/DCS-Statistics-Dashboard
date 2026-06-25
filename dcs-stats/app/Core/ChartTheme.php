<?php

namespace DcsStats\Core;

final class ChartTheme
{
    public static function defaults(): array
    {
        return [
            'chart_primary_color' => '#4CAF50',
            'chart_secondary_color' => '#2196F3',
            'chart_grid_color' => '#2d4a2f',
            'chart_text_color' => '#e0e0e0',
            'home_chart_primary_color' => '#4CAF50',
            'home_chart_secondary_color' => '#2196F3',
            'home_chart_danger_color' => '#f44336',
            'home_chart_warning_color' => '#ffc107',
            'home_chart_grid_color' => '#2d4a2f',
            'home_chart_text_color' => '#e0e0e0',
            'home_top_pilots_color' => '#4CAF50',
            'home_top_pilots_grid_color' => '#2d4a2f',
            'home_top_pilots_text_color' => '#e0e0e0',
            'home_combat_kills_color' => '#2196F3',
            'home_combat_deaths_color' => '#f44336',
            'home_combat_text_color' => '#e0e0e0',
            'home_squadrons_color' => '#ffc107',
            'home_squadrons_grid_color' => '#2d4a2f',
            'home_squadrons_text_color' => '#e0e0e0',
            'home_activity_color' => '#4CAF50',
            'home_activity_grid_color' => '#2d4a2f',
            'home_activity_text_color' => '#e0e0e0',
        ];
    }

    public static function path(): string
    {
        $primaryPath = DCS_ROOT_PATH . '/site-config/data/chart_theme.json';
        $primaryDir = dirname($primaryPath);

        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0700, true);
        }

        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }

        $tempDir = sys_get_temp_dir() . '/dcs_stats';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }

        return $tempDir . '/chart_theme.json';
    }

    public static function load(): array
    {
        $defaults = self::defaults();
        $themePath = self::path();

        if (!file_exists($themePath)) {
            return $defaults;
        }

        $saved = json_decode((string)@file_get_contents($themePath), true);
        if (!is_array($saved)) {
            return $defaults;
        }

        return array_merge($defaults, array_intersect_key($saved, $defaults));
    }

    public static function save(array $theme): bool
    {
        $defaults = self::defaults();
        $cleanTheme = [];

        foreach ($defaults as $key => $defaultValue) {
            $value = $theme[$key] ?? $defaultValue;
            $cleanTheme[$key] = preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : $defaultValue;
        }

        return @file_put_contents(self::path(), json_encode($cleanTheme, JSON_PRETTY_PRINT)) !== false;
    }
}
