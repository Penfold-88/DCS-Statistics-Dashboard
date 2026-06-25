<?php

namespace DcsStats\Services\Admin;

final class HeaderImageSettingsStore
{
    private ThemeAssetService $assetService;
    private HeaderImageSettingsSanitizer $sanitizer;
    private HeaderImageCssBuilder $cssBuilder;

    public function __construct(
        ThemeAssetService $assetService,
        ?HeaderImageSettingsSanitizer $sanitizer = null,
        ?HeaderImageCssBuilder $cssBuilder = null
    ) {
        $this->assetService = $assetService;
        $this->sanitizer = $sanitizer ?? new HeaderImageSettingsSanitizer($assetService);
        $this->cssBuilder = $cssBuilder ?? new HeaderImageCssBuilder($assetService);
    }

    public function load(): array
    {
        $settings = $this->assetService->defaultHeaderImageSettings();
        $path = $this->assetService->headerImageSettingsPath();
        if (file_exists($path)) {
            $saved = json_decode((string)file_get_contents($path), true);
            if (is_array($saved)) {
                $settings = array_merge($settings, array_intersect_key($saved, $settings));
            }
        }

        return $this->sanitizer->clean($settings);
    }

    public function save(array $settings): bool
    {
        $dataDir = DCS_ROOT_PATH . '/site-config/data';
        if (!is_dir($dataDir) && !mkdir($dataDir, 0755, true) && !is_dir($dataDir)) {
            return false;
        }

        $settings = $this->sanitizer->clean($settings);
        $css = $this->cssBuilder->build($settings);

        return file_put_contents(
            $this->assetService->headerImageSettingsPath(),
            json_encode($settings, JSON_PRETTY_PRINT)
        ) !== false
            && file_put_contents(DCS_ROOT_PATH . '/header_custom.css', $css) !== false;
    }
}
