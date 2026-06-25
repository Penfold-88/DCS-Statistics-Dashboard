<?php

namespace DcsStats\Core;

final class VersionTracker
{
    public static function currentInfo(): array
    {
        return (new VersionMetadataStore())->currentInfo();
    }

    public static function installedBuildLabel(?array $versionInfo = null): string
    {
        $versionInfo = is_array($versionInfo) ? $versionInfo : self::currentInfo();
        $version = $versionInfo['version'] ?? (defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'Unknown');

        if (!empty($versionInfo['manual_download']) || empty($versionInfo['commit_sha'])) {
            return $version . ' (Manual Download)';
        }

        return $version;
    }

    public static function githubBranchVersionInfo($repo, $branch, $timeoutSeconds = 5): ?array
    {
        return (new VersionBranchInspector())->inspect($repo, $branch, $timeoutSeconds);
    }

    public static function updateMetadata($version = null, $branch = null, $username = null, $commitSha = null, $commitDate = null): array
    {
        return (new VersionMetadataStore())->update($version, $branch, $username, $commitSha, $commitDate);
    }

    public static function initialize(): array
    {
        return (new VersionMetadataStore())->initialize();
    }
}
