<?php

namespace DcsStats\Services\Admin;

final class ThemeAssetService
{
    private ?HeaderImageSettingsStore $settingsStore;
    private ThemeImagePolicy $imagePolicy;

    public function __construct(?HeaderImageSettingsStore $settingsStore = null, ?ThemeImagePolicy $imagePolicy = null)
    {
        $this->settingsStore = $settingsStore;
        $this->imagePolicy = $imagePolicy ?? new ThemeImagePolicy();
    }

    public function defaultHeaderImageSettings(): array
    {
        return [
            'image' => 'dcs-header-image.jpg',
            'position_x' => 50,
            'position_y' => 50,
            'branding_mode' => 'text',
            'logo' => '',
            'logo_height' => 72,
            'background_image' => '',
            'background_position_x' => 50,
            'background_position_y' => 50,
            'background_zoom' => 125,
        ];
    }

    public function headerImageSettingsPath(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/header_image.json';
    }

    public function isAllowedImageFile($filePath, $extension = null): bool
    {
        return $this->imagePolicy->isAllowedFile($filePath, $extension);
    }

    public function normalizeImagePath($path, bool $allowDefaultHeader = false): string
    {
        return $this->imagePolicy->normalizePath($path, $allowDefaultHeader);
    }

    public function cssImageUrl($path): string
    {
        return $this->imagePolicy->cssUrl($path);
    }

    public function loadHeaderImageSettings(): array
    {
        return $this->settingsStore()->load();
    }

    public function saveHeaderImageSettings(array $settings): bool
    {
        return $this->settingsStore()->save($settings);
    }

    private function settingsStore(): HeaderImageSettingsStore
    {
        if ($this->settingsStore === null) {
            $this->settingsStore = new HeaderImageSettingsStore($this);
        }

        return $this->settingsStore;
    }
}
