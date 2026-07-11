<!-- Admin Overview -->
<div class="overview-grid">
    <div class="overview-card">
        <div>
            <div class="overview-card-header">
                <div class="overview-icon">⚙️</div>
                <div>
                    <h2 class="overview-title"><?= e(dcs_t('admin.dashboard.site_setup')) ?></h2>
                    <div class="overview-subtitle"><?= e(dcs_t('admin.dashboard.site_setup_subtitle')) ?></div>
                </div>
            </div>
            <div class="overview-value"><?= e($siteName) ?></div>
            <div class="overview-meta"><?= e(dcs_t('admin.dashboard.theme')) ?>: <?= e($siteConfig['theme'] ?? 'dark') ?></div>
        </div>
        <span class="status-pill good"><?= e(dcs_t('admin.status.configured')) ?></span>
    </div>

    <div class="overview-card">
        <div>
            <div class="overview-card-header">
                <div class="overview-icon">🔌</div>
                <div>
                    <h2 class="overview-title"><?= e(dcs_t('admin.dashboard.api_connection')) ?></h2>
                    <div class="overview-subtitle"><?= e(dcs_t('admin.dashboard.api_connection_subtitle')) ?></div>
                </div>
            </div>
            <div class="overview-value"><?= e($apiEnabled ? dcs_t('admin.status.enabled') : dcs_t('admin.status.disabled')) ?></div>
            <div class="overview-meta"><?= $apiHost ? e($apiHost) : e(dcs_t('admin.dashboard.no_api_host')) ?></div>
            <div class="overview-meta"><?= e(dcs_t('admin.dashboard.endpoints_enabled', ['count' => number_format($enabledEndpoints)])) ?></div>
        </div>
        <span class="status-pill <?= $apiEnabled && $apiHost ? 'good' : 'warn' ?>"><?= e($apiEnabled && $apiHost ? dcs_t('admin.status.ready') : dcs_t('admin.status.needs_setup')) ?></span>
    </div>

    <div class="overview-card">
        <div>
            <div class="overview-card-header">
                <div class="overview-icon">🎛️</div>
                <div>
                    <h2 class="overview-title"><?= e(dcs_t('admin.dashboard.site_features')) ?></h2>
                    <div class="overview-subtitle"><?= e(dcs_t('admin.dashboard.site_features_subtitle')) ?></div>
                </div>
            </div>
            <div class="overview-value"><?= number_format($enabledFeatureCount) ?> / <?= number_format($featureCount) ?></div>
            <div class="overview-meta"><?= e(dcs_t('admin.dashboard.features_enabled')) ?></div>
        </div>
        <span class="status-pill info"><?= e(dcs_t('admin.status.customisable')) ?></span>
    </div>

    <div class="overview-card" id="dashboard-update-card" data-can-manage-updates="<?= hasPermission('manage_updates') ? '1' : '0' ?>">
        <div>
            <div class="overview-card-header">
                <div class="overview-icon">🔄</div>
                <div>
                    <h2 class="overview-title" id="dashboard-update-title"><?= e(dcs_t('admin.dashboard.update_status')) ?></h2>
                    <div class="overview-subtitle" id="dashboard-update-subtitle"><?= e(dcs_t('admin.dashboard.update_status_subtitle')) ?></div>
                </div>
            </div>
            <div class="overview-value" id="dashboard-update-build"><?= e($installedBuild) ?></div>
            <div class="overview-meta" id="dashboard-update-channel"><?= e($updateChannel['channel']) ?> <?= e(dcs_t('admin.dashboard.channel')) ?>: <?= e($updateChannel['branch']) ?></div>
            <div class="overview-meta" id="dashboard-update-commit"><?= e(dcs_t('admin.dashboard.commit')) ?>: <?= e($installedCommit) ?></div>
            <div class="overview-meta" id="dashboard-update-date" style="display: none;"></div>
            <div class="overview-update-action" id="dashboard-update-action" style="display: none;">
                <a href="update.php" class="btn btn-primary btn-small"><?= e(dcs_t('admin.update.update_now')) ?></a>
            </div>
        </div>
        <span class="status-pill <?= $updateChannel['is_dev'] ? 'warn' : 'good' ?>" id="dashboard-update-pill"><?= $updateChannel['is_dev'] ? 'Dev' : 'Stable' ?></span>
    </div>

    <div class="overview-card">
        <div>
            <div class="overview-card-header">
                <div class="overview-icon">🛠️</div>
                <div>
                    <h2 class="overview-title"><?= e(dcs_t('admin.dashboard.maintenance')) ?></h2>
                    <div class="overview-subtitle"><?= e(dcs_t('admin.dashboard.maintenance_subtitle')) ?></div>
                </div>
            </div>
            <div class="overview-value"><?= e(!empty($maintenanceConfig['enabled']) ? dcs_t('admin.status.on') : dcs_t('admin.status.off')) ?></div>
            <div class="overview-meta"><?= e(dcs_t('admin.dashboard.allowed_ips', ['count' => number_format(count($maintenanceConfig['ip_whitelist'] ?? []))])) ?></div>
        </div>
        <span class="status-pill <?= !empty($maintenanceConfig['enabled']) ? 'warn' : 'good' ?>"><?= e(!empty($maintenanceConfig['enabled']) ? dcs_t('admin.status.restricted') : dcs_t('admin.status.public')) ?></span>
    </div>

    <div class="overview-card">
        <div>
            <div class="overview-card-header">
                <div class="overview-icon">💾</div>
                <div>
                    <h2 class="overview-title"><?= e(dcs_t('admin.dashboard.local_settings')) ?></h2>
                    <div class="overview-subtitle"><?= e(dcs_t('admin.dashboard.local_settings_subtitle')) ?></div>
                </div>
            </div>
            <div class="overview-value"><?= number_format(count($dataFiles)) ?></div>
            <div class="overview-meta"><?= e(dcs_t('admin.dashboard.json_files_found')) ?></div>
        </div>
        <span class="status-pill info"><?= e(dcs_t('admin.status.backup_ready')) ?></span>
    </div>
</div>
