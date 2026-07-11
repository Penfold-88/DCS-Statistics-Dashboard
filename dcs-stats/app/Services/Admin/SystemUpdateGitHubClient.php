<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateGitHubClient
{
    private const MAX_ARCHIVE_BYTES = 104857600;

    public function fetchBranch(string $repo, string $branch): array
    {
        if (!$this->validRepository($repo) || !$this->validRef($branch)) {
            return ['data' => false, 'error' => 'Invalid repository or branch', 'http_code' => 0];
        }

        $branchUrl = 'https://api.github.com/repos/' . $repo . '/branches/' . rawurlencode($branch);
        $ch = curl_init($branchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        $this->secureCurl($ch);
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
        if (!$this->validRepository($repo)) {
            return null;
        }

        $releaseUrl = "https://api.github.com/repos/$repo/releases/latest";
        $ch = curl_init($releaseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        $this->secureCurl($ch);
        $releaseData = curl_exec($ch);
        curl_close($ch);

        if (!$releaseData) {
            return null;
        }

        $release = json_decode($releaseData, true);
        return is_array($release) ? ($release['tag_name'] ?? null) : null;
    }

    public function fetchCommit(string $repo, string $ref): array
    {
        if (!$this->validRepository($repo) || !$this->validRef($ref)) {
            return ['data' => false, 'error' => 'Invalid repository or ref', 'http_code' => 0];
        }

        $url = 'https://api.github.com/repos/' . $repo . '/commits/' . rawurlencode($ref);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        $this->secureCurl($ch);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['data' => $data, 'error' => $error, 'http_code' => $httpCode];
    }

    public function downloadArchive(string $apiUrl, string $zipFile): array
    {
        if (!$this->allowedGitHubUrl($apiUrl)) {
            return ['success' => false, 'http_code' => 0, 'error' => 'Update URL is not allowed'];
        }

        $ch = curl_init($apiUrl);
        $fp = fopen($zipFile, 'wb');
        if ($fp === false) {
            return ['success' => false, 'http_code' => 0, 'error' => 'Could not create update archive'];
        }
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, static function ($resource, $downloadSize, $downloaded): int {
            return $downloadSize > self::MAX_ARCHIVE_BYTES || $downloaded > self::MAX_ARCHIVE_BYTES ? 1 : 0;
        });
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.github.v3+json',
        ]);
        $this->secureCurl($ch);
        if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) {
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        }
        if (defined('CURLOPT_REDIR_PROTOCOLS') && defined('CURLPROTO_HTTPS')) {
            curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
        }
        $download = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        $size = is_file($zipFile) ? (int)filesize($zipFile) : 0;
        $success = $download !== false && $httpCode === 200 && $size > 0 && $size <= self::MAX_ARCHIVE_BYTES;
        $success = $success && $this->allowedGitHubUrl($effectiveUrl);

        return [
            'success' => $success,
            'http_code' => $httpCode,
            'error' => $error,
            'sha256' => $success ? hash_file('sha256', $zipFile) : null,
            'size' => $size,
        ];
    }

    public function validRepository(string $repo): bool
    {
        return preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo) === 1;
    }

    public function validRef(string $ref): bool
    {
        return strlen($ref) <= 128
            && preg_match('#^[A-Za-z0-9][A-Za-z0-9._/-]*$#', $ref) === 1
            && strpos($ref, '..') === false
            && substr($ref, -1) !== '/';
    }

    private function secureCurl($ch): void
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        if (
            PHP_OS_FAMILY === 'Windows' &&
            defined('CURLOPT_SSL_OPTIONS') &&
            defined('CURLSSLOPT_NATIVE_CA')
        ) {
            curl_setopt($ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
        }
        if (defined('CURL_SSLVERSION_TLSv1_2')) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        }
    }

    private function allowedGitHubUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts) || strtolower((string)($parts['scheme'] ?? '')) !== 'https') {
            return false;
        }

        $host = strtolower((string)($parts['host'] ?? ''));
        return in_array($host, ['api.github.com', 'github.com', 'codeload.github.com'], true)
            && !isset($parts['user'])
            && !isset($parts['pass']);
    }
}
