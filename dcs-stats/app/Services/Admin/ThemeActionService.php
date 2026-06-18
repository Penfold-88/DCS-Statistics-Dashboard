<?php

namespace DcsStats\Services\Admin;

final class ThemeActionService
{
    private ThemeConfigurationActionDispatcher $configurationActions;
    private ThemeAppearanceActionDispatcher $appearanceActions;

    public function __construct(
        ?ThemePresetActionService $presetActions = null,
        ?ThemeSettingsActionService $settingsActions = null,
        ?ThemeMenuActionService $menuActions = null,
        ?ThemeUploadService $uploadActions = null,
        ?ThemeColorActionService $colorActions = null,
        ?ThemeConfigurationActionDispatcher $configurationActions = null,
        ?ThemeAppearanceActionDispatcher $appearanceActions = null,
        ?ThemeActionLogger $logger = null
    ) {
        $logger = $logger ?? new ThemeActionLogger();
        $this->configurationActions = $configurationActions ?? new ThemeConfigurationActionDispatcher(
            $presetActions,
            $settingsActions,
            $menuActions,
            $logger
        );
        $this->appearanceActions = $appearanceActions ?? new ThemeAppearanceActionDispatcher(
            $uploadActions,
            $colorActions,
            $logger
        );
    }

    public function handle(
        array $post,
        array $files,
        string $menuConfigFile,
        bool $isAirBoss,
        array $menuItems
    ): array {
        $action = $post['action'] ?? '';

        if (in_array($action, [
            'apply_theme_preset',
            'save_theme_preset',
            'delete_theme_preset',
            'export_theme_settings',
            'import_theme_settings',
            'update_menu',
        ], true)) {
            return $this->configurationActions->handle($action, $post, $files, $menuConfigFile, $menuItems);
        }

        return $this->appearanceActions->handle($action, $post, $files, $isAirBoss, $menuItems);
    }
}
