<?php

namespace DcsStats\Services\Admin;

final class GitRepositoryLocator
{
    public function resolve(string $dashboardRoot): ?string
    {
        foreach ([$dashboardRoot, dirname($dashboardRoot)] as $candidate) {
            $realPath = realpath($candidate);
            if (!$realPath || !$this->isAllowed($realPath, $dashboardRoot)) {
                continue;
            }

            if (is_dir($realPath . '/.git') || is_file($realPath . '/.git')) {
                return $realPath;
            }
        }

        return null;
    }

    private function isAllowed(string $repoPath, string $dashboardRoot): bool
    {
        return $repoPath === $dashboardRoot || $repoPath === dirname($dashboardRoot);
    }
}
