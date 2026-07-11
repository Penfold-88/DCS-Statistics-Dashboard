<?php

namespace DcsStats\Services\Admin;

final class UpdateCheckService
{
    private UpdateCheckGitHubClient $githubClient;

    public function __construct(?UpdateCheckGitHubClient $githubClient = null)
    {
        $this->githubClient = $githubClient ?? new UpdateCheckGitHubClient();
    }

    public function buildReport(): string
    {
        \DcsStats\Core\SupportBootstrap::updateChannel();
        \DcsStats\Core\SupportBootstrap::versionTracker();

        $channelConfig = getUpdateChannelConfig();
        $repo = $channelConfig['repo'];
        $branch = $channelConfig['branch'];
        $versionInfo = getCurrentVersionInfo();
        $currentVersion = $versionInfo['version'] ?? (defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : '1.0.0');
        $installedBuild = getInstalledBuildLabel($versionInfo);
        $lines = [];

        $lines[] = "Current Version: $currentVersion";
        if (!empty($versionInfo['version'])) {
            $lines[] = "Installed Build: {$installedBuild}";
        }
        if (!empty($versionInfo['commit_sha'])) {
            $lines[] = "Installed Commit: " . substr($versionInfo['commit_sha'], 0, 12);
        }
        if (!empty($versionInfo['commit_date'])) {
            $lines[] = "Installed Date: " . date('Y-m-d H:i:s', strtotime($versionInfo['commit_date']));
        }
        $lines[] = "Update Channel: {$channelConfig['channel']}";
        $lines[] = "GitHub Branch: $branch";
        $lines[] = "----------------------------------------";
        $lines[] = "";
        $lines[] = "Checking selected GitHub branch...";

        $branchResult = $this->githubClient->branch($repo, $branch);
        if ($branchResult['http_code'] !== 200 && $branch === 'master') {
            $lines[] = "Master branch not found. Checking fallback branch: main";
            $branch = 'main';
            $branchResult = $this->githubClient->branch($repo, $branch);
        }

        if ($branchResult['http_code'] === 200 && !empty($branchResult['body'])) {
            $branchInfo = json_decode($branchResult['body'], true);
            $sha = $branchInfo['commit']['sha'] ?? null;
            $commitDate = $branchInfo['commit']['commit']['committer']['date'] ?? null;

            if ($sha) {
                $lines[] = "Latest Commit: " . substr($sha, 0, 12);
                if ($commitDate) {
                    $lines[] = "Latest Date: " . date('Y-m-d H:i:s', strtotime($commitDate));
                }

                if (!empty($versionInfo['source_unverified'])) {
                    $lines[] = "";
                    $lines[] = "⚠ Installed source commit is unverified.";
                    $lines[] = "The updater cannot determine whether this manual installation matches $branch.";
                } elseif (!empty($versionInfo['commit_sha']) && strtolower($versionInfo['commit_sha']) === strtolower($sha)) {
                    $lines[] = "";
                    $lines[] = "✓ You are running the latest $branch build.";
                } else {
                    $lines[] = "";
                    $lines[] = "✅ Update Available!";
                    $lines[] = "Click Update Now to download the latest code from $branch.";
                }
            }
        } else {
            $lines[] = "Could not fetch branch information.";
            $lines[] = "HTTP Code: {$branchResult['http_code']}";
            if (!empty($branchResult['error'])) {
                $lines[] = "Connection Error: {$branchResult['error']}";
            }
        }

        $lines[] = "";
        $lines[] = "----------------------------------------";
        $lines[] = "Available versions for downgrade:";

        foreach ($this->githubClient->releaseTags($repo) as $tagName) {
            $marker = version_compare($currentVersion, $tagName, '==') ? ' (current)' : '';
            $lines[] = "- " . $tagName . $marker;
        }

        return implode("\n", $lines) . "\n";
    }

}
