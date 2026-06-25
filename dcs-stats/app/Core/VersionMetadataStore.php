<?php

namespace DcsStats\Core;

final class VersionMetadataStore
{
    public function currentInfo(): array
    {
        $channelConfig = UpdateChannel::config();
        $info = [
            'version' => defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'V1.2',
            'branch' => 'main',
            'commit_sha' => null,
            'commit_date' => null,
            'updated_at' => null,
            'updated_by' => null,
        ];

        $metaFile = $this->path();
        if (file_exists($metaFile)) {
            $meta = json_decode((string)file_get_contents($metaFile), true);
            if (is_array($meta)) {
                $info['version'] = $meta['version'] ?? $info['version'];
                $info['branch'] = $meta['branch'] ?? $info['branch'];
                $info['commit_sha'] = $meta['commit_sha'] ?? null;
                $info['commit_date'] = $meta['commit_date'] ?? null;
                $info['updated_at'] = $meta['updated_at'] ?? null;
                $info['updated_by'] = $meta['updated_by'] ?? null;
            }
        }

        // Older installers recorded the latest remote branch commit even though
        // a manual source download contains no trustworthy commit identity.
        if (($info['updated_by'] ?? null) === 'installer') {
            $info['commit_sha'] = null;
            $info['commit_date'] = null;
            $info['source_unverified'] = true;
        }

        if (empty($info['commit_sha']) && defined('ADMIN_PANEL_VERSION')) {
            $info['version'] = ADMIN_PANEL_VERSION;
            $info['manual_download'] = true;
            $info['source_unverified'] = true;
        }

        if (!empty($channelConfig['is_dev'])) {
            $info['branch'] = $channelConfig['branch'] ?? 'Dev';
            $info['is_dev_override'] = true;
        }

        return $info;
    }

    public function update($version = null, $branch = null, $username = null, $commitSha = null, $commitDate = null): array
    {
        $info = $this->currentInfo();

        if ($version !== null) {
            $info['version'] = $version;
        }
        if ($branch !== null) {
            $info['branch'] = $branch;
        }
        if ($commitSha !== null) {
            $info['commit_sha'] = $commitSha;
        }
        if ($commitDate !== null) {
            $info['commit_date'] = $commitDate;
        }

        $info['updated_at'] = date('Y-m-d H:i:s');
        $info['updated_by'] = $username ?? 'system';

        $this->write([
            'version' => $info['version'],
            'branch' => $info['branch'],
            'commit_sha' => $info['commit_sha'],
            'commit_date' => $info['commit_date'],
            'updated_at' => $info['updated_at'],
            'updated_by' => $info['updated_by'],
        ]);

        return $info;
    }

    public function recordManualInstall(string $version, string $branch, string $username = 'installer'): array
    {
        $info = [
            'version' => $version,
            'branch' => $branch,
            'commit_sha' => null,
            'commit_date' => null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $username,
        ];

        $this->write($info);
        $info['manual_download'] = true;
        $info['source_unverified'] = true;

        return $info;
    }

    public function initialize(): array
    {
        $info = $this->currentInfo();
        if (!file_exists($this->path())) {
            $this->update($info['version'], $info['branch'], 'initial-setup');
        }

        return $info;
    }

    private function path(): string
    {
        return DCS_ROOT_PATH . '/.version_meta.json';
    }

    private function write(array $info): void
    {
        file_put_contents($this->path(), json_encode($info, JSON_PRETTY_PRINT), LOCK_EX);
    }
}
