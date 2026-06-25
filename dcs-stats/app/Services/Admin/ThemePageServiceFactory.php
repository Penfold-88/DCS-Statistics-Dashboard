<?php

namespace DcsStats\Services\Admin;

final class ThemePageServiceFactory
{
    public function create(): ThemePageService
    {
        $assetService = new ThemeAssetService();
        $colorService = new ThemeColorService();
        $menuConfigService = new ThemeMenuConfigService();
        $menuService = new ThemeMenuService();
        $presetStorageService = new ThemePresetStorageService();
        $presetService = new ThemePresetService(
            new ThemePresetCatalog($colorService),
            $colorService,
            $presetStorageService
        );
        $uploadService = new ThemeUploadService($assetService);
        $logger = new ThemeActionLogger();

        $actionService = new ThemeActionService(
            new ThemePresetActionService($presetStorageService, $presetService),
            new ThemeSettingsActionService(new ThemeSettingsBackupService($colorService, $assetService)),
            new ThemeMenuActionService($menuService),
            $uploadService,
            new ThemeColorActionService($colorService),
            null,
            null,
            $logger
        );

        return new ThemePageService(
            $assetService,
            $colorService,
            $menuConfigService,
            $presetService,
            $menuService,
            $uploadService,
            $actionService,
            $presetStorageService,
            new ThemePreviewUrlBuilder(),
            new ChartThemeFieldCatalog(),
            new HeaderImageFieldCatalog()
        );
    }
}
