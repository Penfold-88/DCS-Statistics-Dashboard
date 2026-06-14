<?php

namespace DcsStats\Services\Admin;

final class HeaderImageFieldCatalog
{
    public function headerPositionControls(): array
    {
        return [
            ['key' => 'position_x', 'label' => \dcs_t('admin.themes.horizontal_position'), 'min' => 0, 'max' => 100],
            ['key' => 'position_y', 'label' => \dcs_t('admin.themes.vertical_position'), 'min' => 0, 'max' => 100],
        ];
    }

    public function backgroundPositionControls(): array
    {
        return [
            ['key' => 'background_position_x', 'label' => \dcs_t('admin.themes.background_horizontal_position'), 'min' => 0, 'max' => 100],
            ['key' => 'background_position_y', 'label' => \dcs_t('admin.themes.background_vertical_position'), 'min' => 0, 'max' => 100],
            ['key' => 'background_zoom', 'label' => \dcs_t('admin.themes.background_zoom'), 'min' => 100, 'max' => 180],
        ];
    }

    public function brandingModes(): array
    {
        return [
            'text' => \dcs_t('admin.themes.text_only'),
            'both' => \dcs_t('admin.themes.logo_and_text'),
            'logo' => \dcs_t('admin.themes.logo_only'),
        ];
    }
}
