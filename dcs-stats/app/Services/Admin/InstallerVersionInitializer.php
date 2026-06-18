<?php

namespace DcsStats\Services\Admin;

final class InstallerVersionInitializer
{
    public function initialize(): void
    {
        \DcsStats\Core\SupportBootstrap::versionTracker();
        \DcsStats\Core\SupportBootstrap::updateChannel();

        if (!defined('ADMIN_PANEL')) {
            define('ADMIN_PANEL', true);
        }

        \DcsStats\Core\AdminConfig::load();
        $channelConfig = \getUpdateChannelConfig();
        $installBranch = $channelConfig['branch'] ?? 'main';
        $githubVersionInfo = \getGitHubBranchVersionInfo($channelConfig['repo'] ?? '', $installBranch);

        \updateVersionMetadata(
            ADMIN_PANEL_VERSION,
            $installBranch,
            'installer',
            $githubVersionInfo['commit_sha'] ?? null,
            $githubVersionInfo['commit_date'] ?? null
        );
    }
}
