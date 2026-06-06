<?php
/**
 * Update channel selection.
 *
 * Stable updates use the public production branch by default.
 * Dropping a hidden .dev file into the dcs-stats folder switches updates to the dev branch.
 */

function getUpdateChannelConfig() {
    $rootPath = dirname(__DIR__);
    $isDev = file_exists($rootPath . '/.dev') || file_exists(dirname($rootPath) . '/.dev') || getenv('DEV_BRANCH') === 'true';
    $stableBranch = getenv('DCS_STATS_STABLE_BRANCH') ?: 'main';
    $devBranch = getenv('DCS_STATS_DEV_BRANCH') ?: 'Dev-20-05-26';

    return [
        'repo' => 'Penfold-88/DCS-Statistics-Dashboard',
        'channel' => $isDev ? 'Dev' : 'Stable',
        'branch' => $isDev ? $devBranch : $stableBranch,
        'is_dev' => $isDev,
        'hidden_file' => $rootPath . '/.dev'
    ];
}
?>
