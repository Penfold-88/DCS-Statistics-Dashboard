<?php

namespace DcsStats\Services;

final class InstallCheckinPayloadBuilder
{
    private InstallCheckinConfiguration $configuration;

    public function __construct(?InstallCheckinConfiguration $configuration = null)
    {
        $this->configuration = $configuration ?? new InstallCheckinConfiguration();
    }

    public function transportPayload(array $payload)
    {
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        $token = $this->configuration->token();
        if ($token !== '') {
            $headers[] = 'X-DCS-Stats-Token: ' . $token;
            $payload['token'] = $token;
            if (json_encode($payload) === false) {
                return false;
            }
        }

        return [$payload, $headers];
    }

    public function checkinPayload(
        array $versionInfo,
        array $updateChannel,
        array $options,
        string $day
    ): array {
        $payload = [
            'project' => 'dcs-statistics-dashboard',
            'version' => (string)($versionInfo['version'] ?? (defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'unknown')),
            'branch' => (string)($updateChannel['branch'] ?? ($versionInfo['branch'] ?? 'unknown')),
            'channel' => (string)($updateChannel['channel'] ?? 'unknown'),
            'day' => $day,
        ];

        if (!empty($versionInfo['commit_sha'])) {
            $payload['commit_sha'] = (string)$versionInfo['commit_sha'];
        }
        if (!empty($options['event'])) {
            $payload['event'] = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$options['event']) ?: 'checkin';
        }

        return $payload;
    }

    public function key(array $payload): string
    {
        return implode('|', [
            (string)($payload['version'] ?? 'unknown'),
            (string)($payload['branch'] ?? 'unknown'),
            (string)($payload['channel'] ?? 'unknown'),
            (string)($payload['commit_sha'] ?? ''),
        ]);
    }
}
