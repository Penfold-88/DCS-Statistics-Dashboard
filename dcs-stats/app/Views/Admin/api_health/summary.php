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
            <div class="health-value"><?= e($formattedRefreshInterval) ?></div>
            <div class="health-note"><?= e(dcs_t('admin.api_health.dashboard_refresh_note')) ?></div>
        </div>
        <div class="health-card">
            <div class="health-label"><?= e(dcs_t('admin.api_health.timeout')) ?></div>
            <div class="health-value"><?= e($formattedTimeout) ?></div>
            <div class="health-note"><?= e(dcs_t('admin.api_health.timeout_note')) ?></div>
        </div>
        <div class="health-card">
            <div class="health-label"><?= e(dcs_t('admin.api.cache_ttl')) ?></div>
            <div class="health-value"><?= e($formattedCacheTtl) ?></div>
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
