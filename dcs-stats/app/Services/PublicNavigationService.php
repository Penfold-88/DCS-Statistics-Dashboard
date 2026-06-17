<?php

namespace DcsStats\Services;

final class PublicNavigationService
{
    private PublicNavigationFeatureService $featureService;

    public function __construct(?PublicNavigationFeatureService $featureService = null)
    {
        $this->featureService = $featureService ?? new PublicNavigationFeatureService();
    }

    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::language();

        $menuItems = $this->menuItems();
        $this->appendLegacyFeatureLinks($menuItems);

        return [
            'menuItems' => $menuItems,
            'customLinks' => $this->featureService->customLinks(),
            'customLinksMenuText' => $this->featureService->customLinksMenuText(),
            'serverScopeEnabled' => isFeatureEnabled('server_scope_filter'),
            'serverCardVisibility' => $this->featureService->serverCardVisibility(),
        ];
    }

    public function menuConfigPath(): string
    {
        return (new MenuConfigPathService())->configPath();
    }

    public function label(array $item): string
    {
        $knownLabels = [
            'index.php' => ['Home', 'nav.home'],
            'leaderboard.php' => ['Leaderboard', 'nav.leaderboard'],
            'pilot_statistics.php' => ['Pilot Statistics', 'nav.pilot_statistics'],
            'pilot_credits.php' => ['Pilot Credits', 'nav.pilot_credits'],
            'squadrons.php' => ['Squadrons', 'nav.squadrons'],
            'servers.php' => ['Servers', 'nav.servers'],
        ];

        if (!empty($item['label_key'])) {
            return dcs_t($item['label_key']);
        }

        $url = $item['url'] ?? '';
        $name = $item['name'] ?? '';
        if (isset($knownLabels[$url]) && $name === $knownLabels[$url][0]) {
            return dcs_t($knownLabels[$url][1]);
        }

        return (string)$name;
    }

    public function shouldShow(array $item): bool
    {
        if (empty($item['enabled'])) {
            return false;
        }

        $itemType = $item['type'] ?? 'page';
        $url = $item['url'] ?? '';
        $pageFeatureMap = [
            'index.php' => 'nav_home',
            'leaderboard.php' => 'nav_leaderboard',
            'pilot_statistics.php' => 'nav_pilot_statistics',
            'pilot_credits.php' => 'nav_pilot_credits',
            'squadrons.php' => 'nav_squadrons',
            'servers.php' => 'nav_servers',
        ];

        if (isset($pageFeatureMap[$url]) && !isFeatureEnabled($pageFeatureMap[$url])) {
            return false;
        }

        if ($url === 'pilot_credits.php' && !isFeatureEnabled('credits_enabled')) {
            return false;
        }

        if ($url === 'squadrons.php' && !isFeatureEnabled('squadrons_enabled')) {
            return false;
        }

        if ($itemType === 'discord' && !isFeatureEnabled('show_discord_link')) {
            return false;
        }

        if ($itemType === 'squadron_homepage' && (!isFeatureEnabled('show_squadron_homepage') || empty(getFeatureValue('squadron_homepage_url')))) {
            return false;
        }

        return true;
    }

    private function menuItems(): array
    {
        $menuConfigFile = $this->menuConfigPath();
        $defaultMenuItems = [
            ['name' => 'Home', 'url' => 'index.php', 'enabled' => true, 'label_key' => 'nav.home'],
            ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true, 'label_key' => 'nav.leaderboard'],
            ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true, 'label_key' => 'nav.pilot_statistics'],
            ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true, 'label_key' => 'nav.pilot_credits'],
            ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true, 'label_key' => 'nav.squadrons'],
            ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true, 'label_key' => 'nav.servers'],
        ];

        if (!file_exists($menuConfigFile)) {
            return $defaultMenuItems;
        }

        $savedMenu = json_decode((string)file_get_contents($menuConfigFile), true);

        return $savedMenu && is_array($savedMenu) ? $savedMenu : $defaultMenuItems;
    }

    private function appendLegacyFeatureLinks(array &$menuItems): void
    {
        $hasDiscordInMenu = false;
        $hasSquadronInMenu = false;
        foreach ($menuItems as $item) {
            if (($item['type'] ?? '') === 'discord') {
                $hasDiscordInMenu = true;
            }
            if (($item['type'] ?? '') === 'squadron_homepage') {
                $hasSquadronInMenu = true;
            }
        }

        if (!$hasDiscordInMenu && isFeatureEnabled('show_discord_link')) {
            $menuItems[] = [
                'name' => 'Discord',
                'url' => getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'),
                'enabled' => true,
                'type' => 'discord',
            ];
        }

        if (!$hasSquadronInMenu && isFeatureEnabled('show_squadron_homepage') && !empty(getFeatureValue('squadron_homepage_url'))) {
            $menuItems[] = [
                'name' => getFeatureValue('squadron_homepage_text', 'Squadron'),
                'url' => getFeatureValue('squadron_homepage_url'),
                'enabled' => true,
                'type' => 'squadron_homepage',
            ];
        }
    }
}
