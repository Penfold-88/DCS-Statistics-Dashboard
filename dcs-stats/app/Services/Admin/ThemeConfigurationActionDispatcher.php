<?php

namespace DcsStats\Services\Admin;

final class ThemeConfigurationActionDispatcher
{
    private ThemePresetActionService $presetActions;
    private ThemeSettingsActionService $settingsActions;
    private ThemeMenuActionService $menuActions;
    private ThemeActionLogger $logger;

    public function __construct(
        ?ThemePresetActionService $presetActions = null,
        ?ThemeSettingsActionService $settingsActions = null,
        ?ThemeMenuActionService $menuActions = null,
        ?ThemeActionLogger $logger = null
    ) {
        $this->presetActions = $presetActions ?? new ThemePresetActionService();
        $this->settingsActions = $settingsActions ?? new ThemeSettingsActionService();
        $this->menuActions = $menuActions ?? new ThemeMenuActionService();
        $this->logger = $logger ?? new ThemeActionLogger();
    }

    public function handle(string $action, array $post, array $files, string $menuConfigFile, array $menuItems): array
    {
        switch ($action) {
            case 'apply_theme_preset':
                return $this->result($this->presetActions->apply($post), 'THEME_PRESET_APPLY', $menuItems);

            case 'save_theme_preset':
                return $this->result($this->presetActions->save($post), 'THEME_PRESET_SAVE', $menuItems);

            case 'delete_theme_preset':
                return $this->result($this->presetActions->delete($post), 'THEME_PRESET_DELETE', $menuItems);

            case 'export_theme_settings':
                $this->settingsActions->export($menuConfigFile);
                break;

            case 'import_theme_settings':
                return $this->result($this->settingsActions->import($files, $menuConfigFile), 'THEME_SETTINGS_IMPORT', $menuItems);

            case 'update_menu':
                $result = $this->menuActions->update($post, $menuConfigFile);
                $updatedMenuItems = $result['success'] ? $result['menuItems'] : $menuItems;
                return $this->result($result, 'MENU_UPDATE', $updatedMenuItems);
        }

        return ['error' => '', 'menuItems' => $menuItems, 'message' => ''];
    }

    private function result(array $result, string $logAction, array $menuItems): array
    {
        if ($result['success']) {
            $this->logger->log($logAction, $result['log_message'] ?? $result['message']);
            return ['error' => '', 'menuItems' => $menuItems, 'message' => $result['message']];
        }

        return ['error' => $result['message'], 'menuItems' => $menuItems, 'message' => ''];
    }
}
