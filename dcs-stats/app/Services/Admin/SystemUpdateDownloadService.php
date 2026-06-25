<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateDownloadService
{
    private SystemUpdateGitHubClient $githubClient;

    public function __construct(?SystemUpdateGitHubClient $githubClient = null)
    {
        $this->githubClient = $githubClient ?? new SystemUpdateGitHubClient();
    }

    public function download(string $apiUrl, string $zipFile, callable $log): bool
    {
        $download = $this->githubClient->downloadArchive($apiUrl, $zipFile);
        if (!$download['success']) {
            $log('Download failed. HTTP Code: ' . $download['http_code']);
            @unlink($zipFile);
            return false;
        }

        @chmod($zipFile, 0600);
        $log('Download complete (' . (int)$download['size'] . ' bytes).');
        $log('Archive SHA-256: ' . $download['sha256']);
        return true;
    }
}
