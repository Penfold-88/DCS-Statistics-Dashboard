<?php

namespace DcsStats\Services;

final class PublicHeaderBrandingService
{
    public function state(): array
    {
        $siteConfig = $this->readJson(DCS_ROOT_PATH . '/site_config.json');
        $headerSettings = $this->headerSettings();
        $headerBranding = $headerSettings['branding'];
        $headerLogoPath = ltrim((string)$headerBranding['logo'], '/');

        if ($headerLogoPath === '' || !file_exists(DCS_ROOT_PATH . '/' . $headerLogoPath)) {
            $headerLogoPath = '';
            if ($headerBranding['branding_mode'] === 'logo') {
                $headerBranding['branding_mode'] = 'text';
            }
        }

        $showHeaderLogo = $headerLogoPath !== ''
            && in_array($headerBranding['branding_mode'], ['logo', 'both'], true);
        $savedBackgroundPath = ltrim((string)($headerSettings['raw']['background_image'] ?? ''), '/');

        return [
            'siteName' => $siteConfig['site_name'] ?? 'DCS Statistics',
            'headerBranding' => $headerBranding,
            'headerLogoPath' => $headerLogoPath,
            'showHeaderLogo' => $showHeaderLogo,
            'showHeaderText' => in_array($headerBranding['branding_mode'], ['text', 'both'], true) || !$showHeaderLogo,
            'hasPageBackgroundImage' => $savedBackgroundPath !== ''
                && file_exists(DCS_ROOT_PATH . '/' . $savedBackgroundPath),
        ];
    }

    private function headerSettings(): array
    {
        $saved = $this->readJson(DCS_ROOT_PATH . '/site-config/data/header_image.json');
        $branding = array_merge([
            'branding_mode' => 'text',
            'logo' => '',
            'logo_height' => 72,
        ], array_intersect_key($saved, [
            'branding_mode' => true,
            'logo' => true,
            'logo_height' => true,
        ]));

        $branding['branding_mode'] = in_array($branding['branding_mode'], ['text', 'logo', 'both'], true)
            ? $branding['branding_mode']
            : 'text';
        $branding['logo_height'] = max(32, min(96, (int)$branding['logo_height']));

        return ['branding' => $branding, 'raw' => $saved];
    }

    private function readJson(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $content = @file_get_contents($path);
        if (!$content) {
            return [];
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
}
