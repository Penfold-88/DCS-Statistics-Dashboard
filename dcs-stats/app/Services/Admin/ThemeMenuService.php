<?php

namespace DcsStats\Services\Admin;

final class ThemeMenuService
{
    public function defaultMenuItems(): array
    {
        $defaultMenuItems = [
            ['name' => 'Home', 'url' => 'index.php', 'enabled' => true, 'type' => 'page'],
            ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true, 'type' => 'page'],
            ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true, 'type' => 'page'],
            ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true, 'type' => 'page'],
            ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true, 'type' => 'page'],
            ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true, 'type' => 'page'],
        ];

        if (\isFeatureEnabled('show_discord_link')) {
            $defaultMenuItems[] = [
                'name' => 'Discord',
                'url' => \getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'),
                'enabled' => true,
                'type' => 'discord',
            ];
        }

        if (\isFeatureEnabled('show_squadron_homepage') && !empty(\getFeatureValue('squadron_homepage_url'))) {
            $defaultMenuItems[] = [
                'name' => \getFeatureValue('squadron_homepage_text', 'Squadron'),
                'url' => \getFeatureValue('squadron_homepage_url'),
                'enabled' => true,
                'type' => 'squadron_homepage',
            ];
        }

        return $defaultMenuItems;
    }

    public function loadMenuItems(string $menuConfigFile): array
    {
        $defaultMenuItems = $this->defaultMenuItems();
        if (!file_exists($menuConfigFile)) {
            return $defaultMenuItems;
        }

        $savedMenu = json_decode((string)file_get_contents($menuConfigFile), true);
        if (!$savedMenu || !is_array($savedMenu)) {
            return $defaultMenuItems;
        }

        foreach ($defaultMenuItems as $defaultItem) {
            $found = false;
            foreach ($savedMenu as &$savedItem) {
                if (
                    ($savedItem['url'] ?? '') === $defaultItem['url'] ||
                    (isset($savedItem['type'], $defaultItem['type']) && $savedItem['type'] === $defaultItem['type'])
                ) {
                    $found = true;
                    if (in_array($defaultItem['type'] ?? '', ['discord', 'squadron_homepage'], true)) {
                        $savedItem['url'] = $defaultItem['url'];
                        $savedItem['name'] = $defaultItem['name'];
                    }
                    break;
                }
            }
            unset($savedItem);

            if (!$found && in_array($defaultItem['type'] ?? '', ['discord', 'squadron_homepage'], true)) {
                $savedMenu[] = $defaultItem;
            }
        }

        return $savedMenu;
    }

    public function menuFromPost(array $post): array
    {
        $newMenuItems = [];
        $menuNames = $post['menu_names'] ?? [];
        $menuUrls = $post['menu_urls'] ?? [];
        $menuEnabled = $post['menu_enabled'] ?? [];
        $menuOrder = $post['menu_order'] ?? [];
        $menuTypes = $post['menu_types'] ?? [];

        foreach ($menuOrder as $index) {
            if (isset($menuNames[$index]) && isset($menuUrls[$index])) {
                $newMenuItems[] = [
                    'name' => $menuNames[$index],
                    'url' => $menuUrls[$index],
                    'enabled' => isset($menuEnabled[$index]),
                    'type' => $menuTypes[$index] ?? 'page',
                ];
            }
        }

        return $newMenuItems;
    }

    public function saveMenu(string $menuConfigFile, array $menuItems): bool
    {
        return @file_put_contents($menuConfigFile, json_encode($menuItems, JSON_PRETTY_PRINT)) !== false;
    }
}
