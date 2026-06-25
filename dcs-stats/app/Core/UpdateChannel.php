<?php

namespace DcsStats\Core;

final class UpdateChannel
{
    public static function devFilePath(): ?string
    {
        $rootPath = DCS_ROOT_PATH;
        $candidatePaths = [
            $rootPath . '/.dev',
            dirname($rootPath) . '/.dev',
            DCS_ROOT_PATH . '/site-config/.dev',
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

    public static function config(): array
    {
        $devFilePath = self::devFilePath();
        $isDev = $devFilePath !== null || getenv('DEV_BRANCH') === 'true';
        $stableBranch = getenv('DCS_STATS_STABLE_BRANCH') ?: 'master';
        $devBranch = getenv('DCS_STATS_DEV_BRANCH') ?: 'Dev-20-05-26';

        return [
            'repo' => 'Penfold-88/DCS-Statistics-Dashboard',
            'channel' => $isDev ? 'Dev' : 'Stable',
            'branch' => $isDev ? $devBranch : $stableBranch,
            'is_dev' => $isDev,
            'hidden_file' => $devFilePath ?: DCS_ROOT_PATH . '/.dev',
        ];
    }
}
