<?php

namespace DcsStats\Services;

final class PublicMenuService
{
    private MenuConfigPathService $pathService;

    public function __construct(?MenuConfigPathService $pathService = null)
    {
        $this->pathService = $pathService ?? new MenuConfigPathService();
    }

    public function configPath(): string
    {
        return $this->pathService->configPath();
    }

    public function items(): array
    {
        $menuItems = $this->storedItems();
        $this->appendLegacyFeatureLinks($menuItems);

        return $menuItems;
    }

    private function storedItems(): array
    {
        $defaultItems = $this->defaultItems();
        $configPath = $this->configPath();
        if (!file_exists($configPath)) {
            return $defaultItems;
        }

        $savedMenu = json_decode((string)file_get_contents($configPath), true);
        return $savedMenu && is_array($savedMenu) ? $savedMenu : $defaultItems;
    }

    private function defaultItems(): array
    {
        return [
            ['name' => 'Home', 'url' => 'index.php', 'enabled' => true, 'label_key' => 'nav.home'],
            ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true, 'label_key' => 'nav.leaderboard'],
            ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true, 'label_key' => 'nav.pilot_statistics'],
            ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true, 'label_key' => 'nav.pilot_credits'],
            ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true, 'label_key' => 'nav.squadrons'],
            ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true, 'label_key' => 'nav.servers'],
        ];
    }

    private function appendLegacyFeatureLinks(array &$menuItems): void
    {
        $types = array_column($menuItems, 'type');

        if (!in_array('discord', $types, true) && \isFeatureEnabled('show_discord_link')) {
            $menuItems[] = [
                'name' => 'Discord',
                'url' => \getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'),
                'enabled' => true,
                'type' => 'discord',
            ];
        }

        if (
            !in_array('squadron_homepage', $types, true)
            && \isFeatureEnabled('show_squadron_homepage')
            && !empty(\getFeatureValue('squadron_homepage_url'))
        ) {
            $menuItems[] = [
                'name' => \getFeatureValue('squadron_homepage_text', 'Squadron'),
                'url' => \getFeatureValue('squadron_homepage_url'),
                'enabled' => true,
                'type' => 'squadron_homepage',
            ];
        }
    }
}
