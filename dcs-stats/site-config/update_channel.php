<?php
/**
 * Update channel selection.
 *
 * Stable updates use the public production branch by default.
 * Dropping a hidden .dev file into the dashboard or project root switches updates to the dev branch.
 */

function getUpdateChannelDevFilePath() {
    $rootPath = dirname(__DIR__);
    $candidatePaths = [
        $rootPath . '/.dev',
        dirname($rootPath) . '/.dev',
        __DIR__ . '/.dev'
    ];

    $cwd = getcwd();
    if (is_string($cwd) && $cwd !== '') {
        $candidatePaths[] = rtrim($cwd, '/\\') . '/.dev';
    }

    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if (is_string($documentRoot) && $documentRoot !== '') {
        $candidatePaths[] = rtrim($documentRoot, '/\\') . '/.dev';
    }

    foreach (array_unique($candidatePaths) as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }

    return null;
}

function getUpdateChannelConfig() {
    $rootPath = dirname(__DIR__);
    $devFilePath = getUpdateChannelDevFilePath();
    $isDev = $devFilePath !== null || getenv('DEV_BRANCH') === 'true';
    $stableBranch = getenv('DCS_STATS_STABLE_BRANCH') ?: 'main';
    $devBranch = getenv('DCS_STATS_DEV_BRANCH') ?: 'Dev-20-05-26';

    return [
        'repo' => 'Penfold-88/DCS-Statistics-Dashboard',
        'channel' => $isDev ? 'Dev' : 'Stable',
        'branch' => $isDev ? $devBranch : $stableBranch,
        'is_dev' => $isDev,
        'hidden_file' => $devFilePath ?: $rootPath . '/.dev'
    ];
}
?>
