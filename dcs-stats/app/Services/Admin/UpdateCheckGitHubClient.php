<?php

namespace DcsStats\Services\Admin;

final class UpdateCheckGitHubClient
{
    public function branch(string $repo, string $branch): array
    {
        $url = "https://api.github.com/repos/$repo/branches/" . rawurlencode($branch);
        return $this->fetch($url);
    }

    public function releaseTags(string $repo): array
    {
        $result = $this->fetch("https://api.github.com/repos/$repo/releases?per_page=10");
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

    private function fetch(string $url): array
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
