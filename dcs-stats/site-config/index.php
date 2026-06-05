<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/update_channel.php';
require_once __DIR__ . '/version_tracker.php';
require_once dirname(__DIR__) . '/site_features.php';
require_once dirname(__DIR__) . '/install_checkin.php';
require_once dirname(__DIR__) . '/api_config_helper.php';
require_once dirname(__DIR__) . '/language.php';

// Require admin login
requireAdmin();
requirePermission('view_dashboard');

// Get current admin
$currentAdmin = getCurrentAdmin();
$installDeleteMessage = '';
$installDeleteMessageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_installer') {
    requirePermission('change_settings');
    requireCSRFToken();

    $installPath = __DIR__ . '/install.php';
    $installRealPath = realpath($installPath);
    $adminRealPath = realpath(__DIR__);
    if (!file_exists($installPath)) {
        $installDeleteMessage = dcs_t('admin.dashboard.install_file_delete_missing');
        $installDeleteMessageType = 'success';
    } elseif (
        $installRealPath === false ||
        $adminRealPath === false ||
        $installRealPath !== $adminRealPath . DIRECTORY_SEPARATOR . 'install.php' ||
        !is_file($installRealPath)
    ) {
        $installDeleteMessage = dcs_t('admin.dashboard.install_file_delete_failed');
        $installDeleteMessageType = 'error';
    } elseif (@unlink($installRealPath)) {
        logAdminAction('INSTALLER_FILE_DELETE', ['file' => 'site-config/install.php']);
        $installDeleteMessage = dcs_t('admin.dashboard.install_file_delete_success');
        $installDeleteMessageType = 'success';
    } else {
        $installDeleteMessage = dcs_t('admin.dashboard.install_file_delete_failed');
        $installDeleteMessageType = 'error';
    }
}

// Get dashboard statistics
$stats = getDashboardStats();
$features = loadSiteFeatures();
$featureGroups = getFeatureGroups();
$maintenanceConfig = loadMaintenanceConfig();
$updateChannel = getUpdateChannelConfig();
$versionInfo = initializeVersionTracking();
runInstallCheckinIfDue($versionInfo, $updateChannel);
$apiConfigResult = loadApiConfigWithFix();
$apiConfig = $apiConfigResult['config'] ?? [];
$siteConfigFile = dirname(__DIR__) . '/site_config.json';
$siteConfig = file_exists($siteConfigFile) ? (json_decode(file_get_contents($siteConfigFile), true) ?: []) : [];

$featureCount = 0;
$enabledFeatureCount = 0;
foreach ($featureGroups as $groupFeatures) {
    foreach ($groupFeatures as $featureKey => $featureLabel) {
        $featureCount++;
        if (!empty($features[$featureKey])) {
            $enabledFeatureCount++;
        }
    }
}

$apiEnabled = !empty($apiConfig['use_api']);
$apiHost = $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiConfig['api_base_url'] ?? '');
$enabledEndpoints = isset($apiConfig['enabled_endpoints']) && is_array($apiConfig['enabled_endpoints'])
    ? count($apiConfig['enabled_endpoints'])
    : 0;
$installedBuild = getInstalledBuildLabel($versionInfo);
$installedCommit = !empty($versionInfo['commit_sha']) ? substr($versionInfo['commit_sha'], 0, 12) : 'Unknown';
$backupDataDir = __DIR__ . '/data';
$dataFiles = is_dir($backupDataDir) ? glob($backupDataDir . '/*.json') : [];
$siteName = $siteConfig['site_name'] ?? 'DCS Statistics';
$installFilePresent = file_exists(__DIR__ . '/install.php');

// Page title
$pageTitle = dcs_t('admin.dashboard.title');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Critical inline CSS for layout */
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .card { max-width: 100%; overflow-x: auto; }
        .compact-card {
            margin-bottom: 18px;
        }
        .compact-card .card-header {
            padding: 14px 18px;
        }
        .compact-card .card-title {
            font-size: 18px;
        }
        .compact-card .data-table td {
            padding: 9px 12px;
        }
        .data-table { width: 100%; table-layout: fixed; }
        .data-table td { word-wrap: break-word; overflow-wrap: break-word; }
        
        /* Bridge Log / Activity List Styles */
        .activity-list {
            max-height: 360px;
            max-width: 100%;
            overflow-y: auto;
        }
        .activity-item {
            padding: 10px 14px;
            border-bottom: 1px solid #444;
            word-wrap: break-word;
            overflow-wrap: break-word;
            transition: background-color 0.2s;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-item:hover { background-color: #333; }
        .activity-time {
            font-size: 12px;
            color: #888;
            margin-bottom: 3px;
            font-style: italic;
        }
        .activity-action {
            margin-bottom: 3px;
            word-break: break-word;
            line-height: 1.45;
        }
        .activity-action strong {
            color: #4CAF50;
            margin-right: 5px;
        }
        .activity-details {
            font-size: 12px;
            color: #aaa;
            background: #1a1a1a;
            padding: 6px 8px;
            border-radius: 4px;
            margin-top: 6px;
            word-break: break-all;
            max-width: 100%;
            overflow-x: auto;
            border-left: 3px solid #4CAF50;
        }
        code {
            background: #1a1a1a;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #ff9800;
            word-break: break-all;
            display: inline-block;
            max-width: 100%;
        }
        .text-muted {
            color: #888;
            font-size: 13px;
        }
        .overview-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            margin-bottom: 28px;
        }
        .install-file-warning {
            align-items: center;
            display: flex;
            gap: 14px;
            justify-content: space-between;
        }
        .install-file-warning form {
            flex: 0 0 auto;
            margin: 0;
        }
        @media (max-width: 700px) {
            .install-file-warning {
                align-items: flex-start;
                flex-direction: column;
            }
        }
        .overview-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 18px;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .overview-card-header {
            align-items: flex-start;
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }
        .overview-icon {
            align-items: center;
            background: rgba(76, 175, 80, 0.14);
            border-radius: 6px;
            color: var(--accent-primary);
            display: flex;
            flex: 0 0 40px;
            font-size: 22px;
            height: 40px;
            justify-content: center;
            width: 40px;
        }
        .overview-title {
            color: var(--text-primary);
            font-size: 16px;
            font-weight: 700;
            line-height: 1.25;
            margin: 0;
        }
        .overview-subtitle {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 3px;
        }
        .overview-value {
            color: var(--text-primary);
            font-size: 24px;
            font-weight: 700;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }
        .overview-meta {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 6px;
            overflow-wrap: anywhere;
        }
        .status-pill {
            align-self: flex-start;
            border-radius: 999px;
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            margin-top: 12px;
            padding: 4px 9px;
        }
        .status-pill.good {
            background: rgba(76, 175, 80, 0.18);
            color: #7bd97f;
        }
        .status-pill.warn {
            background: rgba(255, 152, 0, 0.18);
            color: #ffc266;
        }
        .status-pill.info {
            background: rgba(33, 150, 243, 0.18);
            color: #78c3ff;
        }
        .overview-card.update-ready {
            border-color: rgba(33, 150, 243, 0.62);
            background: rgba(33, 150, 243, 0.09);
        }
        .overview-card.update-ready .overview-icon {
            background: rgba(33, 150, 243, 0.18);
            color: #78c3ff;
        }
        .overview-update-action {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .overview-update-action .btn {
            padding: 5px 10px;
        }
        .quick-action-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
        }
        .quick-action-grid .btn {
            padding: 9px 12px;
        }
        .dashboard-lower-grid {
            align-items: start;
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(360px, 1.25fr) minmax(280px, 0.75fr);
        }
        .dashboard-utility-stack {
            display: grid;
            gap: 18px;
        }
        .system-table {
            font-size: 13px;
        }
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .admin-wrapper { flex-direction: column; }
            .admin-content { padding: 15px; }
            .activity-item { padding: 10px; }
            .activity-details { font-size: 11px; padding: 5px; }
            .dashboard-lower-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'nav.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <h1><?= $pageTitle ?></h1>
                <div class="admin-user-menu">
                    <div class="admin-user-info">
                        <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                        <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
                    </div>
                    <a href="logout.php" class="btn btn-secondary btn-small"><?= e(dcs_t('admin.common.logout')) ?></a>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <!-- Welcome Message -->
                <div class="alert alert-info">
                    <?= e(dcs_t('admin.dashboard.welcome', ['user' => $currentAdmin['username']])) ?>
                    <?= e(dcs_t('admin.dashboard.last_watch')) ?>: <?= formatDate($currentAdmin['last_login']) ?>
                </div>

                <?php if ($installDeleteMessage !== ''): ?>
                <div class="alert alert-<?= e($installDeleteMessageType) ?>">
                    <?= e($installDeleteMessage) ?>
                </div>
                <?php endif; ?>

                <?php if ($installFilePresent): ?>
                <div class="alert alert-warning install-file-warning">
                    <div>
                        <strong><?= e(dcs_t('admin.dashboard.install_file_warning_title')) ?>:</strong>
                        <?= e(dcs_t('admin.dashboard.install_file_warning_text')) ?>
                    </div>
                    <?php if (hasPermission('change_settings')): ?>
                    <form method="POST" action="index.php" onsubmit='return confirm(<?= json_encode(dcs_t('admin.dashboard.install_file_delete_confirm')) ?>);'>
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_installer">
                        <button type="submit" class="btn btn-danger btn-small"><?= e(dcs_t('admin.dashboard.install_file_delete_button')) ?></button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

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
                
                <div class="dashboard-lower-grid">
                    <!-- Recent Activity -->
                    <div class="card compact-card">
                        <div class="card-header">
                            <h2 class="card-title"><?= e(dcs_t('admin.dashboard.bridge_log')) ?></h2>
                            <a href="logs.php" class="btn btn-primary btn-small"><?= e(dcs_t('admin.common.view_all')) ?></a>
                        </div>
                        
                        <?php if (empty($stats['recent_activity'])): ?>
                            <p class="text-muted"><?= e(dcs_t('admin.dashboard.no_recent_activity')) ?></p>
                        <?php else: ?>
                            <div class="activity-list">
                                <?php foreach ($stats['recent_activity'] as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-time"><?= formatDate($activity['created_at'] ?? '') ?></div>
                                        <div class="activity-action">
                                            <strong><?= e($activity['admin_username'] ?? dcs_t('home.unknown')) ?></strong>
                                            <?= e(LOG_ACTIONS[$activity['action'] ?? ''] ?? ($activity['action'] ?? dcs_t('home.unknown'))) ?>
                                            <?php if (!empty($activity['target_type'])): ?>
                                                <div style="margin-top: 5px;">
                                                    <span class="text-muted"><?= e(dcs_t('admin.dashboard.target')) ?>: <?= e($activity['target_type']) ?></span>
                                                    <?php if (!empty($activity['target_id'])): ?>
                                                        <code style="font-size: 11px;"><?= e(substr($activity['target_id'], 0, 50)) ?><?= strlen($activity['target_id']) > 50 ? '...' : '' ?></code>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($activity['details'])): ?>
                                            <div class="activity-details">
                                                <?php 
                                                $details = is_array($activity['details']) ? json_encode($activity['details']) : $activity['details'];
                                                echo e(substr($details, 0, 100)) . (strlen($details) > 100 ? '...' : '');
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="dashboard-utility-stack">
                        <!-- Quick Actions -->
                        <div class="card compact-card">
                            <div class="card-header">
                                <h2 class="card-title"><?= e(dcs_t('admin.dashboard.quick_actions')) ?></h2>
                            </div>
                            <div class="quick-action-grid">
                                <?php if (hasPermission('manage_admins')): ?>
                                    <a href="admins.php" class="btn btn-primary"><?= e(dcs_t('admin.dashboard.manage_admins')) ?></a>
                                <?php endif; ?>
                                <?php if (hasPermission('manage_features')): ?>
                                    <a href="settings.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.site_features')) ?></a>
                                    <a href="settings_backup.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.settings_backup')) ?></a>
                                <?php endif; ?>
                                <?php if (hasPermission('manage_api')): ?>
                                    <a href="api_settings.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.api_settings')) ?></a>
                                <?php endif; ?>
                                <?php if (hasPermission('manage_themes')): ?>
                                    <a href="themes.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.themes')) ?></a>
                                <?php endif; ?>
                                <?php if (hasPermission('manage_updates')): ?>
                                    <a href="update.php" class="btn btn-secondary"><?= e(dcs_t('admin.dashboard.updates')) ?></a>
                                <?php endif; ?>
                                <?php if (hasPermission('view_logs')): ?>
                                    <a href="logs.php" class="btn btn-secondary"><?= e(dcs_t('admin.dashboard.view_logs')) ?></a>
                                <?php endif; ?>
                                <?php if (hasPermission('export_data')): ?>
                                    <a href="export.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.export_data')) ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- System Information -->
                        <div class="card compact-card">
                            <div class="card-header">
                                <h2 class="card-title"><?= e(dcs_t('admin.dashboard.system_information')) ?></h2>
                            </div>
                            <table class="data-table system-table">
                                <tr>
                                    <td><?= e(dcs_t('admin.dashboard.admin_panel_version')) ?></td>
                                    <td><?= ADMIN_PANEL_VERSION ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(dcs_t('admin.dashboard.php_version')) ?></td>
                                    <td><?= phpversion() ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(dcs_t('admin.dashboard.storage_mode')) ?></td>
                                    <td><?= e(USE_DATABASE ? dcs_t('admin.dashboard.database') : dcs_t('admin.dashboard.file_based')) ?></td>
                                </tr>
                                <tr>
                                    <td><?= e(dcs_t('admin.dashboard.data_directory')) ?></td>
                                    <td title="<?= htmlspecialchars(ADMIN_DATA_DIR) ?>"><?= basename(rtrim(ADMIN_DATA_DIR, '/')) ?>/</td>
                                </tr>
                                <tr>
                                    <td><?= e(dcs_t('admin.dashboard.log_retention')) ?></td>
                                    <td><?= e(dcs_t('admin.dashboard.days', ['count' => LOG_RETENTION_DAYS])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        const dashboardUpdateText = <?= json_encode([
            'checking' => dcs_t('admin.update.checking_updates'),
            'updateReady' => dcs_t('admin.update.update_ready'),
            'upToDate' => dcs_t('admin.update.up_to_date'),
            'upToDateDetail' => dcs_t('admin.update.up_to_date_detail'),
            'githubFailed' => dcs_t('admin.update.github_check_failed'),
            'latestCodeAvailable' => dcs_t('admin.update.latest_code_available'),
            'latestCommit' => dcs_t('admin.update.latest_commit'),
            'latestDate' => dcs_t('admin.update.latest_date'),
            'commit' => dcs_t('admin.dashboard.commit'),
            'unavailable' => dcs_t('admin.update.unavailable')
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

        function parseDashboardUpdateValue(text, label) {
            const escapedLabel = label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const pattern = new RegExp(escapedLabel + ':\\s*([^\\n\\r]+)', 'i');
            const match = text.match(pattern);
            return match ? match[1].trim() : '';
        }

        function checkDashboardUpdateStatus() {
            const card = document.getElementById('dashboard-update-card');
            if (!card) return;

            const title = document.getElementById('dashboard-update-title');
            const subtitle = document.getElementById('dashboard-update-subtitle');
            const build = document.getElementById('dashboard-update-build');
            const commit = document.getElementById('dashboard-update-commit');
            const date = document.getElementById('dashboard-update-date');
            const pill = document.getElementById('dashboard-update-pill');
            const action = document.getElementById('dashboard-update-action');
            const canManageUpdates = card.dataset.canManageUpdates === '1';

            fetch('api/check_updates.php', { cache: 'no-store' })
                .then(response => response.text())
                .then(data => {
                    const latestCommit = parseDashboardUpdateValue(data, dashboardUpdateText.latestCommit);
                    const latestDate = parseDashboardUpdateValue(data, dashboardUpdateText.latestDate);

                    if (data.includes('✅ Update Available!')) {
                        card.classList.add('update-ready');
                        title.textContent = dashboardUpdateText.updateReady;
                        subtitle.textContent = dashboardUpdateText.latestCodeAvailable;
                        if (latestCommit) {
                            commit.textContent = `${dashboardUpdateText.latestCommit}: ${latestCommit}`;
                        }
                        if (latestDate) {
                            date.style.display = '';
                            date.textContent = `${dashboardUpdateText.latestDate}: ${latestDate}`;
                        }
                        pill.className = 'status-pill info';
                        pill.textContent = dashboardUpdateText.updateReady;
                        if (canManageUpdates) {
                            action.style.display = '';
                        }
                    } else if (data.includes('✅ Already up to date')) {
                        title.textContent = dashboardUpdateText.upToDate;
                        subtitle.textContent = dashboardUpdateText.upToDateDetail;
                        if (latestCommit) {
                            commit.textContent = `${dashboardUpdateText.latestCommit}: ${latestCommit}`;
                        }
                        if (latestDate) {
                            date.style.display = '';
                            date.textContent = `${dashboardUpdateText.latestDate}: ${latestDate}`;
                        }
                    } else if (data.includes('Could not fetch branch information') || data.includes('Could not check')) {
                        title.textContent = dashboardUpdateText.githubFailed;
                        pill.className = 'status-pill warn';
                        pill.textContent = dashboardUpdateText.unavailable;
                    }
                })
                .catch(() => {
                    if (pill) {
                        pill.className = 'status-pill warn';
                        pill.textContent = dashboardUpdateText.unavailable;
                    }
                });
        }

        checkDashboardUpdateStatus();

        // Auto-refresh activity every 30 seconds
        setInterval(() => {
            // In a real implementation, this would fetch new activity via AJAX
        }, 30000);
    </script>
</body>
</html>
