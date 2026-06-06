<?php
/**
 * API Health / Debug Page
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/../api_config_helper.php';
require_once __DIR__ . '/../language.php';

requireAdmin();
requirePermission('manage_api');

$currentAdmin = getCurrentAdmin();
$configResult = loadApiConfigWithFix();
$apiConfig = $configResult['config'];
$configFile = $configResult['config_path'];
$pageTitle = dcs_t('admin.api_health.title');

$apiBaseUrl = rtrim((string)($apiConfig['api_base_url'] ?? ''), '/');
$apiHost = $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiBaseUrl);
$timeout = max(1, (int)($apiConfig['timeout'] ?? 30));
$refreshInterval = max(60, (int)($apiConfig['refresh_interval'] ?? 300));
$cacheTtl = max(0, (int)($apiConfig['cache_ttl'] ?? 300));
$hasApiKey = !empty($apiConfig['api_key']);

$healthEndpoints = [
    ['label' => dcs_t('servers.title'), 'method' => 'GET', 'endpoint' => '/servers', 'note' => dcs_t('admin.api_health.note_servers')],
    ['label' => dcs_t('admin.api_health.server_stats'), 'method' => 'GET', 'endpoint' => '/serverstats', 'note' => dcs_t('admin.api_health.note_serverstats')],
    ['label' => dcs_t('admin.api_health.attendance'), 'method' => 'GET', 'endpoint' => '/server_attendance', 'note' => dcs_t('admin.api_health.note_attendance')],
    ['label' => dcs_t('leaderboard.title'), 'method' => 'GET', 'endpoint' => '/leaderboard?what=kills&limit=1', 'note' => dcs_t('admin.api_health.note_leaderboard')],
    ['label' => dcs_t('squadrons.title'), 'method' => 'GET', 'endpoint' => '/squadrons', 'note' => dcs_t('admin.api_health.note_squadrons')],
    ['label' => dcs_t('admin.api_health.current_server'), 'method' => 'GET', 'endpoint' => '/current_server', 'note' => dcs_t('admin.api_health.note_current_server')]
];

function formatSeconds($seconds) {
    $seconds = (int)$seconds;
    if ($seconds >= 3600 && $seconds % 3600 === 0) {
        return dcs_t($seconds === 3600 ? 'admin.api_health.one_hour' : 'admin.api_health.hours', ['count' => ($seconds / 3600)]);
    }
    if ($seconds >= 60 && $seconds % 60 === 0) {
        return dcs_t('admin.api_health.minutes', ['count' => ($seconds / 60)]);
    }
    return dcs_t('admin.api_health.seconds', ['count' => $seconds]);
}

function summarizePayload($raw) {
    $decoded = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return dcs_t('admin.api_health.non_json_response', ['bytes' => strlen((string)$raw)]);
    }

    if (is_array($decoded)) {
        if (array_is_list($decoded)) {
            return dcs_t(count($decoded) === 1 ? 'admin.api_health.one_list_item' : 'admin.api_health.list_items', ['count' => count($decoded)]);
        }

        if (isset($decoded['items']) && is_array($decoded['items'])) {
            return dcs_t(count($decoded['items']) === 1 ? 'admin.api_health.one_item_in_items' : 'admin.api_health.items_in_items', ['count' => count($decoded['items'])]);
        }

        if (isset($decoded['error'])) {
            return dcs_t('admin.api_health.error_prefix') . ': ' . (is_scalar($decoded['error']) ? $decoded['error'] : dcs_t('admin.api_health.error_object'));
        }

        $keys = array_slice(array_keys($decoded), 0, 8);
        return dcs_t('admin.api_health.object_keys') . ': ' . implode(', ', $keys);
    }

    return dcs_t('admin.api_health.json_type', ['type' => gettype($decoded)]);
}

function runHealthCheck($baseUrl, $apiKey, $timeout, $endpoint) {
    if ($baseUrl === '') {
        return [
            'status' => dcs_t('admin.api_health.not_configured'),
            'ok' => false,
            'http_code' => null,
            'time_ms' => null,
            'summary' => dcs_t('admin.api_health.empty_base_url')
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'status' => dcs_t('admin.api_health.unavailable'),
            'ok' => false,
            'http_code' => null,
            'time_ms' => null,
            'summary' => dcs_t('admin.api_health.curl_unavailable')
        ];
    }

    $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint['endpoint'], '/');
    $started = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $endpoint['method']);

    $headers = ['Accept: application/json'];
    if (!empty($apiKey)) {
        $headers[] = 'X-API-Key: ' . $apiKey;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $raw = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $timeMs = (int)round((microtime(true) - $started) * 1000);

    if ($error) {
        return [
            'status' => dcs_t('admin.api_health.failed'),
            'ok' => false,
            'http_code' => $httpCode ?: null,
            'time_ms' => $timeMs,
            'summary' => $error
        ];
    }

    return [
        'status' => ($httpCode >= 200 && $httpCode < 300) ? dcs_t('admin.api_health.ok') : 'HTTP ' . $httpCode,
        'ok' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'time_ms' => $timeMs,
        'summary' => summarizePayload($raw)
    ];
}

$runChecks = isset($_POST['run_checks']) && verifyCSRFToken($_POST['csrf_token'] ?? '');
$checkResults = [];
if ($runChecks) {
    foreach ($healthEndpoints as $endpoint) {
        $checkResults[$endpoint['endpoint']] = runHealthCheck($apiBaseUrl, $apiConfig['api_key'] ?? '', $timeout, $endpoint);
    }
    logAdminActivity('API_HEALTH_CHECK', $_SESSION['admin_id'], 'settings', 'api_health', ['endpoints' => count($healthEndpoints)]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .health-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .health-card { background: var(--bg-tertiary); border: 1px solid var(--border-primary); border-radius: 8px; padding: 18px; }
        .health-label { color: var(--text-secondary); font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
        .health-value { color: var(--text-primary); font-size: 1.1rem; font-weight: 700; overflow-wrap: anywhere; }
        .health-note { color: var(--text-secondary); font-size: 0.86rem; margin-top: 6px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 760px; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border-primary); text-align: left; vertical-align: top; }
        th { color: var(--accent-primary); background: var(--bg-tertiary); }
        code { color: var(--accent-primary); }
        .status-pill { display: inline-flex; padding: 4px 10px; border-radius: 999px; border: 1px solid var(--border-primary); font-weight: 700; }
        .status-pill.ok { color: var(--accent-success); border-color: var(--accent-success); }
        .status-pill.fail { color: var(--accent-danger); border-color: var(--accent-danger); }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin: 18px 0 24px; }
    </style>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php include __DIR__ . '/nav.php'; ?>

        <main class="admin-main">
            <div class="admin-content">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.api_health.heading')) ?></h2>
                    </div>

                    <div class="health-grid">
                        <div class="health-card">
                            <div class="health-label"><?= e(dcs_t('admin.api.host')) ?></div>
                            <div class="health-value"><?= e($apiHost ?: dcs_t('admin.api_health.not_configured')) ?></div>
                            <div class="health-note"><?= e($apiBaseUrl ?: dcs_t('admin.api_health.no_base_url_saved')) ?></div>
                        </div>
                        <div class="health-card">
                            <div class="health-label"><?= e(dcs_t('admin.api_health.dashboard_refresh')) ?></div>
                            <div class="health-value"><?= e(formatSeconds($refreshInterval)) ?></div>
                            <div class="health-note"><?= e(dcs_t('admin.api_health.dashboard_refresh_note')) ?></div>
                        </div>
                        <div class="health-card">
                            <div class="health-label"><?= e(dcs_t('admin.api_health.timeout')) ?></div>
                            <div class="health-value"><?= e(formatSeconds($timeout)) ?></div>
                            <div class="health-note"><?= e(dcs_t('admin.api_health.timeout_note')) ?></div>
                        </div>
                        <div class="health-card">
                            <div class="health-label"><?= e(dcs_t('admin.api.cache_ttl')) ?></div>
                            <div class="health-value"><?= e(formatSeconds($cacheTtl)) ?></div>
                            <div class="health-note"><?= e(dcs_t('admin.api_health.cache_note')) ?></div>
                        </div>
                        <div class="health-card">
                            <div class="health-label"><?= e(dcs_t('admin.api_health.api_key')) ?></div>
                            <div class="health-value"><?= e($hasApiKey ? dcs_t('admin.status.configured') : dcs_t('admin.api_health.not_configured')) ?></div>
                            <div class="health-note"><?= e(dcs_t('admin.api_health.api_key_note')) ?></div>
                        </div>
                        <div class="health-card">
                            <div class="health-label"><?= e(dcs_t('admin.api_health.config_file')) ?></div>
                            <div class="health-value"><?= e(basename($configFile)) ?></div>
                            <div class="health-note"><?= e($configFile) ?></div>
                        </div>
                    </div>

                    <form method="POST" class="actions">
                        <?= csrfField() ?>
                        <button type="submit" name="run_checks" value="1" class="btn btn-primary"><?= e(dcs_t('admin.api_health.run_check')) ?></button>
                        <a href="api_settings.php" class="btn btn-secondary"><?= e(dcs_t('admin.api.title')) ?></a>
                    </form>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th><?= e(dcs_t('admin.api_health.endpoint')) ?></th>
                                    <th><?= e(dcs_t('admin.api_health.purpose')) ?></th>
                                    <th><?= e(dcs_t('admin.update.status')) ?></th>
                                    <th><?= e(dcs_t('admin.api_health.response')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($healthEndpoints as $endpoint): ?>
                                <?php $result = $checkResults[$endpoint['endpoint']] ?? null; ?>
                                <tr>
                                    <td><code><?= e($endpoint['method'] . ' ' . $endpoint['endpoint']) ?></code></td>
                                    <td><?= e($endpoint['note']) ?></td>
                                    <td>
                                        <?php if ($result): ?>
                                            <span class="status-pill <?= $result['ok'] ? 'ok' : 'fail' ?>"><?= e($result['status']) ?></span>
                                            <?php if ($result['time_ms'] !== null): ?>
                                                <div class="health-note"><?= e($result['time_ms']) ?> ms</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="status-pill"><?= e(dcs_t('admin.api_health.not_checked')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($result['summary'] ?? dcs_t('admin.api_health.run_to_test')) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card" style="margin-top: 24px;">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.api_health.configured_endpoint_map')) ?></h2>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th><?= e(dcs_t('admin.api_health.dashboard_key')) ?></th>
                                    <th><?= e(dcs_t('admin.api_health.api_route')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($apiConfig['endpoints'] ?? []) as $key => $route): ?>
                                <tr>
                                    <td><?= e($key) ?></td>
                                    <td><code><?= e($route) ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
