<?php

namespace DcsStats\Services\Admin;

final class ThemePageService
{
    private ThemeAssetService $assetService;
    private ThemeColorService $colorService;
    private ThemePresetService $presetService;
    private ThemeUploadService $uploadService;
    private ThemeActionService $actionService;
    private ThemePresetStorageService $presetStorageService;
    private ThemePreviewUrlBuilder $previewUrlBuilder;
    private ChartThemeFieldCatalog $chartThemeFieldCatalog;
    private HeaderImageFieldCatalog $headerImageFieldCatalog;

    public function __construct(
        ?ThemeAssetService $assetService = null,
        ?ThemeColorService $colorService = null,
        ?ThemeMenuConfigService $menuConfigService = null,
        ?ThemePresetService $presetService = null,
        ?ThemeMenuService $menuService = null,
        ?ThemeUploadService $uploadService = null,
        ?ThemeActionService $actionService = null,
        ?ThemePresetStorageService $presetStorageService = null,
        ?ThemePreviewUrlBuilder $previewUrlBuilder = null,
        ?ChartThemeFieldCatalog $chartThemeFieldCatalog = null,
        ?HeaderImageFieldCatalog $headerImageFieldCatalog = null
    ) {
        $this->assetService = $assetService ?? new ThemeAssetService();
        $this->colorService = $colorService ?? new ThemeColorService();
        $this->presetService = $presetService ?? new ThemePresetService();
        $this->uploadService = $uploadService ?? new ThemeUploadService($this->assetService);
        $this->actionService = $actionService ?? new ThemeActionService();
        $this->presetStorageService = $presetStorageService ?? new ThemePresetStorageService();
        $this->previewUrlBuilder = $previewUrlBuilder ?? new ThemePreviewUrlBuilder();
        $this->chartThemeFieldCatalog = $chartThemeFieldCatalog ?? new ChartThemeFieldCatalog();
        $this->headerImageFieldCatalog = $headerImageFieldCatalog ?? new HeaderImageFieldCatalog();
    }

    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $isAirBoss = ($currentAdmin['role'] === ROLE_AIR_BOSS);
        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!\verifyCSRFToken(\getRequestCSRFToken())) {
                $error = 'Invalid request token';
            } elseif ($demoRestricted) {
                $error = \demoWriteLockMessage();
            } else {
                $actionState = $this->actionService->handle(
                    $_POST,
                    $_FILES,
                    '',
                    $isAirBoss,
                    []
                );
                $message = $actionState['message'];
                $error = $actionState['error'];
            }
        }

        $customCss = DCS_ROOT_PATH . '/custom_theme.css';
        $customColors = $this->colorService->loadColorsFromCss($customCss);
        $themeOptions = $this->colorService->loadOptionsFile($customCss);
        $headerImageSettings = $this->assetService->loadHeaderImageSettings();

        return [
            'backgroundPreviewImage' => !empty($headerImageSettings['background_image']) ? '../' . ltrim($headerImageSettings['background_image'], '/') : '',
            'backups' => $this->uploadService->listBackups(),
            'builtInThemePresets' => $this->presetService->builtInPresets(),
            'chartColors' => \loadChartTheme(),
            'chartThemeFieldGroups' => $this->chartThemeFieldCatalog->groups(),
            'csrfToken' => \getCSRFToken(),
            'customColors' => $customColors,
            'customThemePresets' => $this->presetStorageService->loadCustomPresets(),
            'currentAdmin' => $currentAdmin,
            'defaultChartTheme' => \getDefaultChartTheme(),
            'defaultThemeColors' => $this->colorService->defaultColors(),
            'demoRestricted' => $demoRestricted,
            'error' => $error,
            'headerImageSettings' => $headerImageSettings,
            'headerImageFieldCatalog' => $this->headerImageFieldCatalog,
            'headerLogoPreview' => !empty($headerImageSettings['logo']) ? '../' . ltrim($headerImageSettings['logo'], '/') : '',
            'headerPreviewImage' => '../' . ltrim($headerImageSettings['image'], '/'),
            'isAirBoss' => $isAirBoss,
            'message' => $message,
            'pageTitle' => \dcs_t('admin.themes.title'),
            'previewUrl' => $this->previewUrlBuilder->build($customColors, $themeOptions),
            'themeColorGroups' => $this->colorService->colorGroups(),
            'themeOptions' => $themeOptions,
        ];
    }
}
