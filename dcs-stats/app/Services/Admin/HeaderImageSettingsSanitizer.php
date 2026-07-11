<?php

namespace DcsStats\Services\Admin;

final class HeaderImageSettingsSanitizer
{
    private ThemeAssetService $assetService;

    public function __construct(ThemeAssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function clean(array $settings): array
    {
        $settings = array_merge($this->assetService->defaultHeaderImageSettings(), $settings);

        $settings['image'] = $this->assetService->normalizeImagePath($settings['image'], true);
        if ($settings['image'] === '') {
            $settings['image'] = $this->assetService->defaultHeaderImageSettings()['image'];
        }

        $settings['position_x'] = $this->percent($settings['position_x']);
        $settings['position_y'] = $this->percent($settings['position_y']);
        $settings['background_position_x'] = $this->percent($settings['background_position_x']);
        $settings['background_position_y'] = $this->percent($settings['background_position_y']);
        $settings['background_zoom'] = max(100, min(180, (int)$settings['background_zoom']));

        if (!in_array($settings['branding_mode'], ['text', 'logo', 'both'], true)) {
            $settings['branding_mode'] = 'text';
        }

        $settings['logo_height'] = max(32, min(96, (int)$settings['logo_height']));
        $settings['logo'] = $this->assetService->normalizeImagePath($settings['logo']);
        if (empty($settings['logo'])) {
            $settings['logo'] = '';
            if ($settings['branding_mode'] === 'logo') {
                $settings['branding_mode'] = 'text';
            }
        }

        $settings['background_image'] = $this->assetService->normalizeImagePath($settings['background_image']);

        return $settings;
    }

    private function percent($value): int
    {
        return max(0, min(100, (int)$value));
    }
}
