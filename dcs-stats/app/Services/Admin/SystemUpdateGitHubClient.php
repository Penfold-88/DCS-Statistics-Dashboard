<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateGitHubClient
{
    public function fetchBranch(string $repo, string $branch): array
    {
        $branchUrl = "https://api.github.com/repos/$repo/branches/" . rawurlencode($branch);
        $ch = curl_init($branchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        $data = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'data' => $data,
            'error' => $error,
            'http_code' => $httpCode,
        ];
    }

    public function latestReleaseTag(string $repo): ?string
    {
        $releaseUrl = "https://api.github.com/repos/$repo/releases/latest";
        $ch = curl_init($releaseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        $releaseData = curl_exec($ch);
        curl_close($ch);

        if (!$releaseData) {
            return null;
        }

        $release = json_decode($releaseData, true);
        return is_array($release) ? ($release['tag_name'] ?? null) : null;
    }

    public function downloadArchive(string $apiUrl, string $zipFile): array
    {
        $ch = curl_init($apiUrl);
        $fp = fopen($zipFile, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.github.v3+json',
        ]);
        $download = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        return [
            'success' => $download !== false && $httpCode === 200,
            'http_code' => $httpCode,
            'error' => $error,
        ];
    }
}
