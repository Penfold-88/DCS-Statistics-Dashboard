<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= e(dcs_t('admin.dashboard.system_information')) ?></h3>
            </div>
            <div class="card-content">
                <div class="version-summary-grid">
                    <div class="version-card">
                        <span class="version-label"><?= e(dcs_t('admin.update.dashboard_version')) ?></span>
                        <span class="version-value"><?= e($dashboardVersion) ?></span>
                    </div>
                    <div class="version-card">
                        <span class="version-label"><?= e(dcs_t('admin.update.installed_build')) ?></span>
                        <span class="version-value"><?= e($installedBuild) ?></span>
                    </div>
                    <div class="version-card">
                        <span class="version-label"><?= e(dcs_t('admin.update.update_channel')) ?></span>
                        <span class="badge badge-<?= $updateChannel['is_dev'] ? 'warning' : 'primary' ?>"><?= e($updateChannel['channel']) ?></span>
                    </div>
                </div>

                <div class="version-details">
                    <div class="version-detail-row">
                        <strong><?= e(dcs_t('admin.update.current_branch')) ?></strong>
                        <span class="badge badge-<?= $updateChannel['is_dev'] ? 'warning' : 'primary' ?>"><?= e($currentBranch) ?></span>
                    </div>
                    <div class="version-detail-row">
                        <strong><?= e(dcs_t('admin.update.github_source')) ?></strong>
                        <code><?= e($updateChannel['repo'] . ':' . $updateChannel['branch']) ?></code>
                    </div>
                    <div class="version-detail-row">
                        <strong><?= e(dcs_t('admin.update.installed_commit')) ?></strong>
                        <code><?= e($installedCommit) ?></code>
                    </div>
                    <div class="version-detail-row">
                        <strong><?= e(dcs_t('admin.update.installed_date')) ?></strong>
                        <span><?= e($installedDate) ?></span>
                    </div>
                    <div class="version-detail-row">
                        <strong><?= e(dcs_t('admin.update.last_updated')) ?></strong>
                        <span><?= e($lastUpdated) ?></span>
                    </div>
                    <div class="version-detail-row">
                        <strong><?= e(dcs_t('admin.dashboard.php_version')) ?></strong>
                        <span><?= e(PHP_VERSION) ?></span>
                    </div>
                </div>

                <button class="btn btn-secondary btn-small" type="button" onclick="copySupportInfo()" style="margin-top: 8px;">
                    <?= e(dcs_t('admin.update.copy_support_info')) ?>
                </button>
                <pre id="support-info" class="update-log" style="display: none; height: auto; max-height: 180px; margin-top: 10px;"><?php foreach ($supportInfo as $label => $value): ?><?= e($label . ': ' . $value) . "\n" ?><?php endforeach; ?></pre>

                <div class="version-status-panel">
                    <div class="version-status-title" id="update-status-title"><?= e(dcs_t('admin.update.checking_github_source')) ?></div>
                    <div class="version-status-meta">
                        <span id="update-status"><?= e(dcs_t('admin.update.checking_updates')) ?></span>
                        <span><?= e(dcs_t('admin.update.latest_commit')) ?>: <code id="remote-commit"><?= e(dcs_t('admin.update.checking')) ?></code></span>
                        <span><?= e(dcs_t('admin.update.latest_date')) ?>: <span id="remote-date"><?= e(dcs_t('admin.update.checking')) ?></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= e(dcs_t('admin.dashboard.quick_actions')) ?></h3>
            </div>
            <div class="card-content">
                <button class="btn btn-secondary btn-block mb-2 demo-lockable" onclick="createBackup()" <?= $demoRestricted ? 'disabled' : '' ?>>
                    <span class="nav-icon">💾</span> <?= e(dcs_t('admin.update.create_backup')) ?>
                </button>
                <button class="btn btn-warning btn-block mb-2 demo-lockable" onclick="showDowngradeModal()" <?= $demoRestricted ? 'disabled' : '' ?>>
                    <span class="nav-icon">⬇️</span> <?= e(dcs_t('admin.update.downgrade_version')) ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><?= e(dcs_t('admin.update.update_log')) ?></h3>
    </div>
    <pre id="log" class="update-log"></pre>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><?= e(dcs_t('admin.update.backup_management')) ?></h3>
    </div>
    <div id="backup-list" class="card-content">
        <p class="text-muted"><?= e(dcs_t('admin.update.loading_backups')) ?></p>
    </div>
</div>
