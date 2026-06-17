<?php

namespace DcsStats\Services\Admin;

final class ThemeMenuActionService
{
    private ThemeMenuService $menuService;

    public function __construct(?ThemeMenuService $menuService = null)
    {
        $this->menuService = $menuService ?? new ThemeMenuService();
    }

    public function update(array $post, string $menuConfigFile): array
    {
        $newMenuItems = $this->menuService->menuFromPost($post);
        if (!$this->menuService->saveMenu($menuConfigFile, $newMenuItems)) {
            return [
                'success' => false,
                'message' => 'Failed to save menu configuration. Please check file permissions.',
                'menuItems' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Menu configuration updated successfully',
            'log_message' => 'Updated navigation menu configuration',
            'menuItems' => $newMenuItems,
        ];
    }
}
