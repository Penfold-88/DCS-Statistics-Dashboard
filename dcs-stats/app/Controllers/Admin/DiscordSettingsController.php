<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\DiscordSettingsPageService;

final class DiscordSettingsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'language',
            'siteFeatures',
        ]);

        $currentAdmin = $this->requirePermission('manage_discord');
        $discordState = (new DiscordSettingsPageService())->state($currentAdmin);

        $this->render('discord_settings.php', $discordState);
    }
}
