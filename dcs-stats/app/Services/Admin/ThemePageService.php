<?php

namespace DcsStats\Services\Admin;

final class ThemePageService
{
    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $isAirBoss = ($currentAdmin['role'] === ROLE_AIR_BOSS);
        $message = '';
        $error = '';

        $themeAssetService = new ThemeAssetService();
        $themeColorService = new ThemeColorService();
        $themeMenuConfigService = new ThemeMenuConfigService();
        $themePresetService = new ThemePresetService();
        $themeMenuService = new ThemeMenuService();
        $themeUploadService = new ThemeUploadService($themeAssetService);

        $menuConfigFile = $themeMenuConfigService->configPath();
        $menuItems = $themeMenuService->loadMenuItems($menuConfigFile);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!\verifyCSRFToken(\getRequestCSRFToken())) {
                $error = 'Invalid request token';
            } elseif ($demoRestricted) {
                $error = \demoWriteLockMessage();
            } else {
                $actionState = (new ThemeActionService())->handle(
                    $_POST,
                    $_FILES,
                    $menuConfigFile,
                    $isAirBoss,
                    $menuItems
                );
                $message = $actionState['message'];
                $error = $actionState['error'];
                $menuItems = $actionState['menuItems'];
            }
        }

        $customCss = DCS_ROOT_PATH . '/custom_theme.css';
        $customColors = $themeColorService->loadColorsFromCss($customCss);
        $themeOptions = $themeColorService->loadOptionsFile($customCss);
        $headerImageSettings = $themeAssetService->loadHeaderImageSettings();

        return [
            'backgroundPreviewImage' => !empty($headerImageSettings['background_image']) ? '../' . ltrim($headerImageSettings['background_image'], '/') : '',
            'backups' => $themeUploadService->listBackups(),
            'builtInThemePresets' => $themePresetService->builtInPresets(),
            'chartColors' => \loadChartTheme(),
            'chartThemeFieldGroups' => (new ChartThemeFieldCatalog())->groups(),
            'csrfToken' => \getCSRFToken(),
            'customColors' => $customColors,
            'customThemePresets' => (new ThemePresetStorageService())->loadCustomPresets(),
            'currentAdmin' => $currentAdmin,
            'defaultChartTheme' => \getDefaultChartTheme(),
            'defaultThemeColors' => $themeColorService->defaultColors(),
            'defaultMenuItems' => $themeMenuService->defaultMenuItems(),
            'demoRestricted' => $demoRestricted,
            'error' => $error,
            'headerImageSettings' => $headerImageSettings,
            'headerLogoPreview' => !empty($headerImageSettings['logo']) ? '../' . ltrim($headerImageSettings['logo'], '/') : '',
            'headerPreviewImage' => '../' . ltrim($headerImageSettings['image'], '/'),
            'isAirBoss' => $isAirBoss,
            'menuItems' => $menuItems,
            'message' => $message,
            'pageTitle' => \dcs_t('admin.themes.title'),
            'previewUrl' => $this->previewUrl($customColors, $themeOptions),
            'themeColorGroups' => $themeColorService->colorGroups(),
            'themeOptions' => $themeOptions,
        ];
    }

    private function previewUrl(array $customColors, array $themeOptions): string
    {
        $previewParams = ['preview' => '1'];
        foreach ($customColors as $colorKey => $colorValue) {
            $previewParams[$colorKey] = ltrim((string)$colorValue, '#');
        }
        $previewParams['header_title_gradient_enabled'] = !empty($themeOptions['header_title_gradient_enabled']) ? '1' : '0';
        $previewParams['page_background_gradient_enabled'] = !empty($themeOptions['page_background_gradient_enabled']) ? '1' : '0';

        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $port = (string)($_SERVER['SERVER_PORT'] ?? '');
        $protocol = ($https || $port === '443') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $currentPath = dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/site-config/themes.php'));
        $parentPath = dirname($currentPath);

        return $protocol . $host . ($parentPath === '/' ? '' : $parentPath) . '/index.php?' . http_build_query($previewParams);
    }
}
