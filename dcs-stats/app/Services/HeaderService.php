<?php

namespace DcsStats\Services;

final class HeaderService
{
    public function state(): array
    {
        require_once DCS_ROOT_PATH . '/config_path.php';
        require_once DCS_ROOT_PATH . '/api_config_helper.php';
        require_once DCS_ROOT_PATH . '/site_metadata.php';
        require_once DCS_ROOT_PATH . '/language.php';
        require_once DCS_ROOT_PATH . '/site-config/demo_helpers.php';

        $this->sendSecurityHeaders();
        $this->exitForMaintenanceIfNeeded();

        $siteConfig = $this->siteConfig();
        $siteName = $siteConfig['site_name'] ?? 'DCS Statistics';
        $headerSettings = $this->headerSettings();
        $headerBranding = $headerSettings['branding'];
        $headerLogoPath = ltrim((string)$headerBranding['logo'], '/');

        if ($headerLogoPath === '' || !file_exists(DCS_ROOT_PATH . '/' . $headerLogoPath)) {
            $headerLogoPath = '';
            if ($headerBranding['branding_mode'] === 'logo') {
                $headerBranding['branding_mode'] = 'text';
            }
        }

        $showHeaderLogo = $headerLogoPath !== '' && in_array($headerBranding['branding_mode'], ['logo', 'both'], true);
        $showHeaderText = in_array($headerBranding['branding_mode'], ['text', 'both'], true) || !$showHeaderLogo;
        $savedBackgroundPath = ltrim((string)($headerSettings['raw']['background_image'] ?? ''), '/');

        return [
            'siteName' => $siteName,
            'siteMetadata' => loadSiteMetadata(),
            'headerBranding' => $headerBranding,
            'headerLogoPath' => $headerLogoPath,
            'showHeaderLogo' => $showHeaderLogo,
            'showHeaderText' => $showHeaderText,
            'hasPageBackgroundImage' => $savedBackgroundPath !== '' && file_exists(DCS_ROOT_PATH . '/' . $savedBackgroundPath),
            'previewColors' => $this->previewColors(),
            'frontendDemoMode' => $this->frontendDemoMode(),
        ];
    }

    private function siteConfig(): array
    {
        $siteConfigFile = DCS_ROOT_PATH . '/site_config.json';
        if (!file_exists($siteConfigFile)) {
            return [];
        }

        $content = @file_get_contents($siteConfigFile);
        if (!$content) {
            return [];
        }

        $config = json_decode($content, true);

        return is_array($config) ? $config : [];
    }

    private function headerSettings(): array
    {
        $settingsPath = DCS_ROOT_PATH . '/site-config/data/header_image.json';
        $branding = [
            'branding_mode' => 'text',
            'logo' => '',
            'logo_height' => 72,
        ];
        $raw = [];

        if (file_exists($settingsPath)) {
            $saved = json_decode((string)file_get_contents($settingsPath), true);
            if (is_array($saved)) {
                $raw = $saved;
                $branding = array_merge($branding, array_intersect_key($saved, $branding));
            }
        }

        $branding['branding_mode'] = in_array($branding['branding_mode'], ['text', 'logo', 'both'], true) ? $branding['branding_mode'] : 'text';
        $branding['logo_height'] = max(32, min(96, (int)$branding['logo_height']));

        return [
            'branding' => $branding,
            'raw' => $raw,
        ];
    }

    private function sendSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        if (isset($_GET['preview']) && $_GET['preview'] === '1') {
            header('X-Frame-Options: SAMEORIGIN');
        } else {
            header('X-Frame-Options: DENY');
        }
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        $cspConnectSrc = "'self' http://localhost:* https://localhost:*";
        $frameAncestors = (isset($_GET['preview']) && $_GET['preview'] === '1') ? " frame-ancestors 'self';" : " frame-ancestors 'none';";
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src {$cspConnectSrc};" . $frameAncestors);
    }

    private function previewColors(): ?array
    {
        if (!isset($_GET['preview']) || $_GET['preview'] !== '1') {
            return null;
        }

        $previewColorKeys = [
            'primary_color', 'secondary_color', 'background_color', 'background_gradient_color', 'surface_color', 'surface_dark_color',
            'card_color', 'card_alt_color', 'card_heading_color', 'card_text_color',
            'card_muted_text_color', 'text_color', 'muted_text_color', 'heading_color', 'link_color',
            'accent_color', 'accent_hover_color', 'border_color', 'nav_background_color',
            'nav_text_color', 'nav_hover_color', 'header_text_color', 'header_title_gradient_color',
            'header_subtitle_color',
            'footer_background_color', 'footer_text_color', 'success_color', 'warning_color',
            'danger_color', 'info_color', 'table_header_color', 'table_header_text_color',
            'table_row_color', 'table_text_color', 'table_player_name_color', 'table_hover_color',
        ];

        $previewColors = [];
        foreach ($previewColorKeys as $key) {
            $value = $_GET[$key] ?? null;
            $previewColors[$key] = (is_string($value) && preg_match('/^[0-9A-Fa-f]{6}$/', $value)) ? '#' . $value : null;
        }

        return $previewColors;
    }

    private function exitForMaintenanceIfNeeded(): void
    {
        $maintenanceFile = DCS_ROOT_PATH . '/site-config/data/maintenance.json';
        if (!file_exists($maintenanceFile)) {
            return;
        }

        $maintenance = json_decode((string)file_get_contents($maintenanceFile), true);
        if (empty($maintenance['enabled'])) {
            return;
        }

        $allowed = $maintenance['ip_whitelist'] ?? [];
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (in_array($ip, $allowed)) {
            return;
        }

        if (!defined('MAINTENANCE_OVERRIDE')) {
            define('MAINTENANCE_OVERRIDE', true);
        }
        require DCS_ROOT_PATH . '/maintenance.php';
        exit;
    }

    private function frontendDemoMode(): bool
    {
        return (function_exists('isDemoMode') && isDemoMode())
            || file_exists(DCS_ROOT_PATH . '/.demo')
            || file_exists(DCS_ROOT_PATH . '/site-config/.demo')
            || file_exists(dirname(DCS_ROOT_PATH) . '/.demo');
    }
}
