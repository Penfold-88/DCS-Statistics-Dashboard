<?php

namespace DcsStats\Services\Admin;

final class UpdateCheckService
{
    public function buildReport(): string
    {
        require_once DCS_ROOT_PATH . '/site-config/update_channel.php';
        require_once DCS_ROOT_PATH . '/site-config/version_tracker.php';

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

        $branchResult = $this->fetchBranchInfo($repo, $branch);
        if ($branchResult['http_code'] !== 200 && $branch === 'master') {
            $lines[] = "Master branch not found. Checking fallback branch: main";
            $branch = 'main';
            $branchResult = $this->fetchBranchInfo($repo, $branch);
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

                if (!empty($versionInfo['commit_sha']) && strtolower($versionInfo['commit_sha']) === strtolower($sha)) {
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

        foreach ($this->fetchReleaseTags($repo) as $tagName) {
            $marker = version_compare($currentVersion, $tagName, '==') ? ' (current)' : '';
            $lines[] = "- " . $tagName . $marker;
        }

        return implode("\n", $lines) . "\n";
    }

    private function fetchBranchInfo(string $repo, string $branch): array
    {
        $url = "https://api.github.com/repos/$repo/branches/" . rawurlencode($branch);
        return $this->fetchGitHub($url);
    }

    private function fetchReleaseTags(string $repo): array
    {
        $result = $this->fetchGitHub("https://api.github.com/repos/$repo/releases?per_page=10");
        if (empty($result['body'])) {
            return [];
        }

        $releases = json_decode($result['body'], true);
        if (!is_array($releases)) {
            return [];
        }

        $tags = [];
        foreach ($releases as $release) {
            if (isset($release['tag_name'])) {
                $tags[] = $release['tag_name'];
            }
        }

        return $tags;
    }

    private function fetchGitHub(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'body' => $body,
            'error' => $error,
            'http_code' => $httpCode,
        ];
    }
}

