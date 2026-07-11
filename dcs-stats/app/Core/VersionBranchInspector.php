<?php

namespace DcsStats\Core;

final class VersionBranchInspector
{
    public function inspect($repo, $branch, $timeoutSeconds = 5): ?array
    {
        if (!function_exists('curl_init') || empty($repo) || empty($branch)) {
            return null;
        }

        $maxResponseBytes = 65536;
        $branchData = '';
        $repoPath = str_replace('%2F', '/', rawurlencode($repo));
        $branchUrl = 'https://api.github.com/repos/' . $repoPath . '/branches/' . rawurlencode($branch);
        $ch = curl_init($branchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Version-Tracker');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeoutSeconds);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSeconds);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$branchData, $maxResponseBytes) {
            $branchData .= $chunk;
            return strlen($branchData) > $maxResponseBytes ? 0 : strlen($chunk);
        });
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $branchData === '' || strlen($branchData) > $maxResponseBytes) {
            return null;
        }

        $branchInfo = json_decode($branchData, true);
        if (!is_array($branchInfo)) {
            return null;
        }

        $commit = $branchInfo['commit'] ?? null;
        if (!is_array($commit)) {
            return null;
        }

        $sha = $commit['sha'] ?? null;
        if (!is_string($sha) || !preg_match('/^[a-f0-9]{40}$/i', $sha)) {
            return null;
        }

        $commitMeta = $commit['commit'] ?? null;
        $committer = is_array($commitMeta) ? ($commitMeta['committer'] ?? null) : null;
        $commitDate = is_array($committer) ? ($committer['date'] ?? null) : null;
        if (!is_string($commitDate) || strtotime($commitDate) === false) {
            $commitDate = null;
        }

        if (strlen((string)$branch) > 100) {
            return null;
        }

        return [
            'branch' => $branch,
            'commit_sha' => $sha,
            'commit_date' => $commitDate,
        ];
    }
}
