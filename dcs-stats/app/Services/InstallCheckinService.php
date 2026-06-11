<?php

namespace DcsStats\Services;

final class InstallCheckinService
{
    public function endpoint(): string
    {
        if (defined('DCS_STATS_INSTALL_CHECKIN_ENDPOINT')) {
            return trim((string)DCS_STATS_INSTALL_CHECKIN_ENDPOINT);
        }

        return '';
    }

    public function token(): string
    {
        if (defined('DCS_STATS_INSTALL_CHECKIN_TOKEN')) {
            return trim((string)DCS_STATS_INSTALL_CHECKIN_TOKEN);
        }

        return '';
    }

    public function allowSslFallback(): bool
    {
        return defined('DCS_STATS_INSTALL_CHECKIN_ALLOW_SSL_FALLBACK')
            ? (bool)DCS_STATS_INSTALL_CHECKIN_ALLOW_SSL_FALLBACK
            : false;
    }

    public function statePath(): string
    {
        $dataDir = DCS_ROOT_PATH . '/site-config/data';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0700, true);
        }

        return $dataDir . '/install_checkin_state.json';
    }

    public function loadState(): array
    {
        $statePath = $this->statePath();
        if (!file_exists($statePath)) {
            return [];
        }

        $state = json_decode((string)@file_get_contents($statePath), true);

        return is_array($state) ? $state : [];
    }

    public function saveState(array $state): void
    {
        @file_put_contents($this->statePath(), json_encode($state, JSON_PRETTY_PRINT));
    }

    public function buildPayload(array $payload)
    {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $token = $this->token();
        if ($token !== '') {
            $headers[] = 'X-DCS-Stats-Token: ' . $token;
            $payload['token'] = $token;
            $json = json_encode($payload);
            if ($json === false) {
                return false;
            }
        }

        return [$payload, $headers];
    }

    public function sendWithCurl(string $endpoint, string $json, array $headers, bool $verifySsl = true): array
    {
        $curl = curl_init($endpoint);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => 500,
            CURLOPT_TIMEOUT_MS => 750,
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
        ]);
        curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errno = (int)curl_errno($curl);
        curl_close($curl);

        return [
            'success' => $status >= 200 && $status < 300,
            'status' => $status,
            'errno' => $errno,
        ];
    }

    public function sendPayload(string $endpoint, array $payload): bool
    {
        [$payload, $headers] = $this->buildPayload($payload);
        $json = json_encode($payload);
        if ($json === false) {
            return false;
        }

        if (function_exists('curl_init')) {
            $result = $this->sendWithCurl($endpoint, $json, $headers, true);
            if ($result['success']) {
                return true;
            }

            if ($result['errno'] === 60 && $this->allowSslFallback()) {
                $fallbackResult = $this->sendWithCurl($endpoint, $json, $headers, false);
                return $fallbackResult['success'];
            }

            return false;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers) . "\r\n",
                'content' => $json,
                'timeout' => 0.75,
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($endpoint, false, $context);

        return $result !== false;
    }

    public function payloadKey(array $payload): string
    {
        return implode('|', [
            (string)($payload['version'] ?? 'unknown'),
            (string)($payload['branch'] ?? 'unknown'),
            (string)($payload['channel'] ?? 'unknown'),
            (string)($payload['commit_sha'] ?? ''),
        ]);
    }

    public function runIfDue(array $versionInfo = [], array $updateChannel = [], array $options = []): array
    {
        $endpoint = $this->endpoint();
        if ($endpoint === '') {
            return ['status' => 'not_configured'];
        }

        if (!preg_match('#^https?://#i', $endpoint)) {
            return ['status' => 'invalid_endpoint'];
        }

        $today = gmdate('Y-m-d');
        $state = $this->loadState();

        $payload = [
            'project' => 'dcs-statistics-dashboard',
            'version' => (string)($versionInfo['version'] ?? (defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'unknown')),
            'branch' => (string)($updateChannel['branch'] ?? ($versionInfo['branch'] ?? 'unknown')),
            'channel' => (string)($updateChannel['channel'] ?? 'unknown'),
            'day' => $today,
        ];

        if (!empty($versionInfo['commit_sha'])) {
            $payload['commit_sha'] = (string)$versionInfo['commit_sha'];
        }

        if (!empty($options['event'])) {
            $payload['event'] = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$options['event']) ?: 'checkin';
        }

        $payloadKey = $this->payloadKey($payload);
        $force = !empty($options['force']);

        if (!$force && ($state['last_success_day'] ?? '') === $today && ($state['last_success_key'] ?? '') === $payloadKey) {
            return ['status' => 'already_sent_today'];
        }

        $lastAttemptTime = strtotime((string)($state['last_attempt_at'] ?? ''));
        if (!$force && $lastAttemptTime && time() - $lastAttemptTime < 1800) {
            return ['status' => 'recent_attempt_wait'];
        }

        $state['last_attempt_day'] = $today;
        $state['last_attempt_at'] = gmdate('c');
        $state['last_attempt_key'] = $payloadKey;

        if ($this->sendPayload($endpoint, $payload)) {
            $state['last_success_day'] = $today;
            $state['last_success_at'] = gmdate('c');
            $state['last_success_key'] = $payloadKey;
            $state['last_payload'] = $payload;
            unset($state['install_id']);
            $this->saveState($state);

            return ['status' => 'sent'];
        }

        unset($state['install_id']);
        $this->saveState($state);

        return ['status' => 'failed'];
    }
}
