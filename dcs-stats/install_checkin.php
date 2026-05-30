<?php
/**
 * Anonymous install/version check-in.
 *
 * Sends one small daily payload when enabled:
 * project, version, branch, channel, and UTC day.
 */

$checkinConfig = __DIR__ . '/install_checkin_config.php';
if (file_exists($checkinConfig)) {
    require_once $checkinConfig;
}

function getInstallCheckinEndpoint() {
    if (defined('DCS_STATS_INSTALL_CHECKIN_ENDPOINT')) {
        return trim((string)DCS_STATS_INSTALL_CHECKIN_ENDPOINT);
    }

    return '';
}

function getInstallCheckinToken() {
    if (defined('DCS_STATS_INSTALL_CHECKIN_TOKEN')) {
        return trim((string)DCS_STATS_INSTALL_CHECKIN_TOKEN);
    }

    return '';
}

function allowInstallCheckinSslFallback() {
    return defined('DCS_STATS_INSTALL_CHECKIN_ALLOW_SSL_FALLBACK')
        ? (bool)DCS_STATS_INSTALL_CHECKIN_ALLOW_SSL_FALLBACK
        : false;
}

function getInstallCheckinStatePath() {
    $dataDir = __DIR__ . '/site-config/data';
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0700, true);
    }

    return $dataDir . '/install_checkin_state.json';
}

function loadInstallCheckinState() {
    $statePath = getInstallCheckinStatePath();
    if (!file_exists($statePath)) {
        return [];
    }

    $state = json_decode((string)@file_get_contents($statePath), true);
    return is_array($state) ? $state : [];
}

function saveInstallCheckinState($state) {
    $statePath = getInstallCheckinStatePath();
    @file_put_contents($statePath, json_encode($state, JSON_PRETTY_PRINT));
}

function buildInstallCheckinPayload($payload) {
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    $token = getInstallCheckinToken();
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

function sendInstallCheckinWithCurl($endpoint, $json, $headers, $verifySsl = true) {
    $curl = curl_init($endpoint);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT_MS => 500,
        CURLOPT_TIMEOUT_MS => 750,
        CURLOPT_SSL_VERIFYPEER => $verifySsl,
        CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0
    ]);
    curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $errno = (int)curl_errno($curl);
    curl_close($curl);

    return [
        'success' => $status >= 200 && $status < 300,
        'status' => $status,
        'errno' => $errno
    ];
}

function sendInstallCheckinPayload($endpoint, $payload) {
    [$payload, $headers] = buildInstallCheckinPayload($payload);
    $json = json_encode($payload);
    if ($json === false) {
        return false;
    }

    if (function_exists('curl_init')) {
        $result = sendInstallCheckinWithCurl($endpoint, $json, $headers, true);
        if ($result['success']) {
            return true;
        }

        if ($result['errno'] === 60 && allowInstallCheckinSslFallback()) {
            $fallbackResult = sendInstallCheckinWithCurl($endpoint, $json, $headers, false);
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
            'ignore_errors' => true
        ]
    ]);

    $result = @file_get_contents($endpoint, false, $context);
    return $result !== false;
}

function runInstallCheckinIfDue($versionInfo = [], $updateChannel = []) {
    $endpoint = getInstallCheckinEndpoint();
    if ($endpoint === '') {
        return ['status' => 'not_configured'];
    }

    if (!preg_match('#^https?://#i', $endpoint)) {
        return ['status' => 'invalid_endpoint'];
    }

    $today = gmdate('Y-m-d');
    $state = loadInstallCheckinState();

    if (($state['last_success_day'] ?? '') === $today) {
        return ['status' => 'already_sent_today'];
    }

    $lastAttemptTime = strtotime((string)($state['last_attempt_at'] ?? ''));
    if ($lastAttemptTime && time() - $lastAttemptTime < 1800) {
        return ['status' => 'recent_attempt_wait'];
    }

    $payload = [
        'project' => 'dcs-statistics-dashboard',
        'version' => (string)($versionInfo['version'] ?? (defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'unknown')),
        'branch' => (string)($updateChannel['branch'] ?? ($versionInfo['branch'] ?? 'unknown')),
        'channel' => (string)($updateChannel['channel'] ?? 'unknown'),
        'day' => $today
    ];

    $state['last_attempt_day'] = $today;
    $state['last_attempt_at'] = gmdate('c');

    if (sendInstallCheckinPayload($endpoint, $payload)) {
        $state['last_success_day'] = $today;
        $state['last_success_at'] = gmdate('c');
        $state['last_payload'] = $payload;
        unset($state['install_id']);
        saveInstallCheckinState($state);
        return ['status' => 'sent'];
    }

    unset($state['install_id']);
    saveInstallCheckinState($state);
    return ['status' => 'failed'];
}
