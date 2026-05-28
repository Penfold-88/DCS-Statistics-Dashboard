<?php
/**
 * Update Dashboard from GitHub branches
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/update_channel.php';
require_once dirname(__DIR__) . '/language.php';

requireAdmin();
requirePermission('manage_updates');

$currentAdmin = getCurrentAdmin();
$updateChannel = getUpdateChannelConfig();

$pageTitle = dcs_t('admin.update.title');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .update-log {
            background: #111;
            color: #0f0;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            font-family: monospace;
        }
        .warning-box {
            background-color: rgba(255, 152, 0, 0.1);
            border: 1px solid var(--accent-warning);
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .col-md-6 {
            flex: 1;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-primary {
            background-color: var(--accent-primary);
            color: white;
        }
        .badge-warning {
            background-color: var(--accent-warning);
            color: #000;
        }
        .btn-block {
            width: 100%;
            display: block;
        }
        .btn-info {
            background-color: var(--accent-info);
            color: white;
        }
        .btn-info:hover {
            background-color: #1976D2;
        }
        .mb-2 {
            margin-bottom: 10px;
        }
        .badge-info {
            background-color: #2196F3;
            color: white;
        }
        .badge-success {
            background-color: #4CAF50;
            color: white;
        }
        .mt-2 {
            margin-top: 10px;
        }
        .version-summary-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            margin-bottom: 16px;
        }
        .version-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px;
        }
        .version-label {
            color: var(--text-muted);
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .version-value {
            color: var(--text-primary);
            display: block;
            font-size: 18px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }
        .version-details {
            display: grid;
            gap: 8px;
            margin: 0 0 14px;
        }
        .version-detail-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: space-between;
        }
        .version-detail-row strong {
            color: var(--text-muted);
        }
        .version-status-panel {
            background: rgba(33, 150, 243, 0.08);
            border: 1px solid rgba(33, 150, 243, 0.22);
            border-radius: 6px;
            margin-top: 14px;
            padding: 12px;
        }
        .version-status-title {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 6px;
        }
        .version-status-meta {
            color: var(--text-muted);
            display: grid;
            gap: 4px;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'nav.php'; ?>
    <main class="admin-main">
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
        <div class="admin-content">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?= e(dcs_t('admin.dashboard.system_information')) ?></h3>
                        </div>
                        <div class="card-content">
                            <?php
                            require_once __DIR__ . '/version_tracker.php';
                            require_once dirname(__DIR__) . '/dev_mode.php';
                            $versionInfo = initializeVersionTracking();
                            $dashboardVersion = defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'Unknown';
                            $currentBranch = $versionInfo['branch'];
                            $isDev = isDevMode();
                            $installedBuild = $versionInfo['version'] ?? 'Unknown';
                            if (empty($versionInfo['commit_sha']) && preg_match('/^V?\d+\.\d+\.\d+/i', $installedBuild)) {
                                $installedBuild .= ' (legacy - refreshes after next update)';
                            }
                            $installedCommit = !empty($versionInfo['commit_sha']) ? substr($versionInfo['commit_sha'], 0, 12) : 'Unknown';
                            $installedDate = !empty($versionInfo['commit_date']) ? date('Y-m-d H:i:s', strtotime($versionInfo['commit_date'])) : 'Unknown';
                            $lastUpdated = $versionInfo['updated_at'] ?? 'Unknown';
                            $supportInfo = [
                                dcs_t('admin.update.dashboard_version') => $dashboardVersion,
                                dcs_t('admin.update.installed_build') => $installedBuild,
                                dcs_t('admin.update.current_branch') => $currentBranch,
                                dcs_t('admin.update.update_channel') => $updateChannel['channel'],
                                dcs_t('admin.update.github_branch') => $updateChannel['branch'],
                                dcs_t('admin.update.installed_commit') => $installedCommit,
                                dcs_t('admin.update.installed_date') => $installedDate,
                                dcs_t('admin.dashboard.php_version') => PHP_VERSION,
                                dcs_t('admin.update.last_updated') => $lastUpdated
                            ];
                            ?>
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
                                    <span class="badge badge-<?= $currentBranch === 'Dev' ? 'warning' : 'primary' ?>"><?= e($currentBranch) ?></span>
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
                            <button class="btn btn-secondary btn-block mb-2" onclick="createBackup()">
                                <span class="nav-icon">💾</span> <?= e(dcs_t('admin.update.create_backup')) ?>
                            </button>
                            <button class="btn btn-warning btn-block mb-2" onclick="showDowngradeModal()">
                                <span class="nav-icon">⬇️</span> <?= e(dcs_t('admin.update.downgrade_version')) ?>
                            </button>
                            <button class="btn btn-info btn-block mb-2" onclick="checkForUpdates()">
                                <span class="nav-icon">🔍</span> <?= e(dcs_t('admin.update.check_for_updates')) ?>
                            </button>
                            <button class="btn btn-primary btn-block mb-2" onclick="performUpdate()">
                                <span class="nav-icon">⬆️</span> <?= e(dcs_t('admin.update.update_now')) ?>
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
        </div>
    </main>
</div>

<!-- Restore Modal -->
<div id="restoreModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?= e(dcs_t('admin.update.restore_backup')) ?></h3>
            <button class="modal-close" onclick="closeModal('restoreModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p><?= e(dcs_t('admin.update.select_backup_restore')) ?></p>
            <div id="restore-backup-list"><?= e(dcs_t('admin.update.loading_backups')) ?></div>
        </div>
    </div>
</div>

<!-- Downgrade Modal -->
<div id="downgradeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?= e(dcs_t('admin.update.downgrade_version')) ?></h3>
            <button class="modal-close" onclick="closeModal('downgradeModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="downgrade-form">
                <div class="form-group">
                    <label for="downgrade-version"><?= e(dcs_t('admin.update.select_version')) ?></label>
                    <select id="downgrade-version" class="form-control">
                        <option value=""><?= e(dcs_t('admin.update.loading_versions')) ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <p class="text-muted"><?= e(dcs_t('admin.update.auto_backup_before_downgrade')) ?></p>
                </div>
                <button type="submit" class="btn btn-warning"><?= e(dcs_t('admin.update.downgrade')) ?></button>
            </form>
        </div>
    </div>
</div>

<script>
// Check for updates on page load
let updateAvailable = false;
let latestVersion = null;
const adminCsrfToken = <?= json_encode(getCSRFToken()) ?>;
const updateText = <?= json_encode([
    'supportCopied' => dcs_t('admin.update.support_copied'),
    'unavailable' => dcs_t('admin.update.unavailable'),
    'updateReady' => dcs_t('admin.update.update_ready'),
    'latestCodeAvailable' => dcs_t('admin.update.latest_code_available'),
    'updateNow' => dcs_t('admin.update.update_now'),
    'upToDate' => dcs_t('admin.update.up_to_date'),
    'upToDateDetail' => dcs_t('admin.update.up_to_date_detail'),
    'githubFailed' => dcs_t('admin.update.github_check_failed'),
    'githubFailedDetail' => dcs_t('admin.update.github_failed_detail'),
    'unknownStatus' => dcs_t('admin.update.unknown_status'),
    'unknownStatusDetail' => dcs_t('admin.update.unknown_status_detail'),
    'failedCheck' => dcs_t('admin.update.failed_check'),
    'startingUpdate' => dcs_t('admin.update.starting_update', ['channel' => $updateChannel['channel']]),
    'backupDate' => dcs_t('admin.update.backup_date'),
    'version' => dcs_t('admin.update.version'),
    'branch' => dcs_t('admin.update.branch'),
    'size' => dcs_t('admin.update.size'),
    'status' => dcs_t('admin.update.status'),
    'actions' => dcs_t('admin.update.actions'),
    'protected' => dcs_t('admin.update.protected'),
    'autoDeleted' => dcs_t('admin.update.auto_deleted'),
    'restore' => dcs_t('admin.update.restore'),
    'delete' => dcs_t('admin.update.delete'),
    'keepsBackups' => dcs_t('admin.update.keeps_backups'),
    'noBackups' => dcs_t('admin.update.no_backups'),
    'failedLoadBackups' => dcs_t('admin.update.failed_load_backups'),
    'confirmRestore' => dcs_t('admin.update.confirm_restore'),
    'startingRestore' => dcs_t('admin.update.starting_restore'),
    'restoreFailed' => dcs_t('admin.update.restore_failed'),
    'confirmDelete' => dcs_t('admin.update.confirm_delete'),
    'failedDelete' => dcs_t('admin.update.failed_delete'),
    'creatingBackup' => dcs_t('admin.update.creating_backup'),
    'checkingUpdates' => dcs_t('admin.update.checking_updates'),
    'failedCheckUpdates' => dcs_t('admin.update.failed_check_updates'),
    'noBackupsAvailable' => dcs_t('admin.update.no_backups_available'),
    'selectVersion' => dcs_t('admin.update.select_version'),
    'pleaseSelectVersion' => dcs_t('admin.update.please_select_version'),
    'downgradingTo' => dcs_t('admin.update.downgrading_to')
], JSON_UNESCAPED_UNICODE) ?>;

function copySupportInfo() {
    const supportInfo = document.getElementById('support-info');
    if (!supportInfo) return;

    supportInfo.style.display = 'block';
    navigator.clipboard.writeText(supportInfo.textContent.trim())
        .then(() => {
            const log = document.getElementById('log');
            log.textContent = updateText.supportCopied + '\n\n' + supportInfo.textContent.trim();
        })
        .catch(() => {
            const range = document.createRange();
            range.selectNodeContents(supportInfo);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        });
}

function checkUpdateStatus() {
    fetch('api/check_updates.php')
        .then(response => response.text())
        .then(data => {
            const statusDiv = document.getElementById('update-status');
            const statusTitle = document.getElementById('update-status-title');
            const remoteCommit = document.getElementById('remote-commit');
            const remoteDate = document.getElementById('remote-date');
            const latestCommitMatch = data.match(/Latest Commit: ([^\n]+)/);
            const latestDateMatch = data.match(/Latest Date: ([^\n]+)/);
            const branchMatch = data.match(/GitHub Branch: ([^\n]+)/);
            const sourceLabel = branchMatch ? branchMatch[1].trim() : 'selected branch';

            if (remoteCommit) {
                remoteCommit.textContent = latestCommitMatch ? latestCommitMatch[1].trim() : updateText.unavailable;
            }
            if (remoteDate) {
                remoteDate.textContent = latestDateMatch ? latestDateMatch[1].trim() : updateText.unavailable;
            }
            
            // Parse the response to check if update is available
            if (data.includes('✅ Update Available!')) {
                updateAvailable = true;
                // Extract version from response
                const versionMatch = data.match(/Latest Release: (v?[\d.]+)/);
                if (versionMatch) {
                    latestVersion = versionMatch[1];
                }
                statusTitle.textContent = updateText.updateReady;
                statusDiv.innerHTML = `${updateText.latestCodeAvailable} ${sourceLabel}. <button class="btn btn-primary btn-small" onclick="performUpdate()" style="margin-left: 10px;">${updateText.updateNow}</button>`;
            } else if (data.includes('✓ You are running the latest')) {
                statusTitle.textContent = updateText.upToDate;
                statusDiv.innerHTML = `<span class="text-success">${updateText.upToDateDetail}</span>`;
            } else if (data.includes('Could not fetch branch information')) {
                statusTitle.textContent = updateText.githubFailed;
                statusDiv.innerHTML = updateText.githubFailedDetail;
            } else {
                statusTitle.textContent = updateText.unknownStatus;
                statusDiv.innerHTML = updateText.unknownStatusDetail;
            }
            
            // Also populate versions for downgrade
            const versions = [];
            const versionMatches = data.matchAll(/- (v?[\d.]+)/g);
            for (const match of versionMatches) {
                versions.push(match[1]);
            }
            populateVersionSelect(versions);
        })
        .catch(error => {
            document.getElementById('update-status').innerHTML = `<p class="text-danger">${updateText.failedCheck}</p>`;
        });
}

function performUpdate() {
    const formData = new FormData();
    formData.append('csrf_token', adminCsrfToken);
    
    const log = document.getElementById('log');
    log.textContent = updateText.startingUpdate + '\n';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/update.php');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onprogress = function() {
        log.textContent = xhr.responseText;
        log.scrollTop = log.scrollHeight;
    };
    xhr.onload = function() {
        log.textContent = xhr.responseText;
        log.scrollTop = log.scrollHeight;
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    };
    xhr.send(formData);
}

// Load backup list
function loadBackups() {
    fetch('api/list_backups.php')
        .then(response => response.json())
        .then(data => {
            const backupList = document.getElementById('backup-list');
            if (data.backups && data.backups.length > 0) {
                let html = '<div class="data-table-wrapper"><table class="data-table">';
                html += `<thead><tr><th>${updateText.backupDate}</th><th>${updateText.version}</th><th>${updateText.branch}</th><th>${updateText.size}</th><th>${updateText.status}</th><th>${updateText.actions}</th></tr></thead><tbody>`;
                data.backups.forEach((backup, index) => {
                    const branchClass = backup.branch === 'Dev' ? 'badge-warning' : 'badge-primary';
                    const isProtected = index < 5;
                    const statusHtml = isProtected 
                        ? `<span class="badge badge-success">${updateText.protected}</span>` 
                        : `<span class="badge badge-warning">${updateText.autoDeleted}</span>`;
                    html += `<tr>
                        <td>${backup.date}</td>
                        <td>${backup.version}</td>
                        <td><span class="badge ${branchClass}">${backup.branch}</span></td>
                        <td>${backup.size}</td>
                        <td>${statusHtml}</td>
                        <td>
                            <button class="btn btn-small btn-secondary" onclick="restoreBackup('${backup.name}')">${updateText.restore}</button>
                            <button class="btn btn-small btn-danger" onclick="deleteBackup('${backup.name}')">${updateText.delete}</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                html += `<p class="text-muted mt-2">${updateText.keepsBackups}</p>`;
                backupList.innerHTML = html;
            } else {
                backupList.innerHTML = `<p class="text-muted">${updateText.noBackups}</p>`;
            }
        })
        .catch(error => {
            document.getElementById('backup-list').innerHTML = `<p class="text-danger">${updateText.failedLoadBackups}</p>`;
        });
}

function restoreBackup(filename) {
    if (!confirm(updateText.confirmRestore)) {
        return;
    }
    
    const log = document.getElementById('log');
    log.textContent = updateText.startingRestore + '\n';
    
    fetch('api/restore_backup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': adminCsrfToken
        },
        body: JSON.stringify({ backup: filename, csrf_token: adminCsrfToken })
    })
    .then(response => response.text())
    .then(data => {
        log.textContent += data;
        loadBackups();
    })
    .catch(error => {
        log.textContent += updateText.restoreFailed + ': ' + error.message;
    });
}

function deleteBackup(filename) {
    if (!confirm(updateText.confirmDelete)) {
        return;
    }
    
    fetch('api/delete_backup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': adminCsrfToken
        },
        body: JSON.stringify({ backup: filename, csrf_token: adminCsrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadBackups();
        } else {
            alert(updateText.failedDelete + ': ' + data.error);
        }
    })
    .catch(error => {
        alert(updateText.failedDelete + ': ' + error.message);
    });
}

// Create manual backup
function createBackup() {
    const log = document.getElementById('log');
    log.textContent = updateText.creatingBackup + '\n';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/create_backup.php');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('X-CSRF-Token', adminCsrfToken);
    xhr.onprogress = function() {
        log.textContent = xhr.responseText;
        log.scrollTop = log.scrollHeight;
    };
    xhr.onload = function() {
        log.textContent = xhr.responseText;
        log.scrollTop = log.scrollHeight;
        loadBackups();
    };
    xhr.send();
}

// Check for updates
function checkForUpdates() {
    const log = document.getElementById('log');
    log.textContent = updateText.checkingUpdates + '\n';
    
    fetch('api/check_updates.php')
        .then(response => response.text())
        .then(data => {
            log.textContent = data;
        })
        .catch(error => {
            log.textContent = updateText.failedCheckUpdates + ': ' + error.message;
        });
}


// Modal functions
function showRestoreModal() {
    document.getElementById('restoreModal').classList.add('active');
    loadBackupsForRestore();
}

function showDowngradeModal() {
    document.getElementById('downgradeModal').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Load backups for restore modal
function loadBackupsForRestore() {
    fetch('api/list_backups.php')
        .then(response => response.json())
        .then(data => {
            const restoreList = document.getElementById('restore-backup-list');
            if (data.backups && data.backups.length > 0) {
                let html = '<div class="backup-list">';
                data.backups.forEach(backup => {
                    const branchClass = backup.branch === 'Dev' ? 'badge-warning' : 'badge-primary';
                    html += `
                        <div class="backup-item" style="padding: 10px; border: 1px solid var(--border-color); margin-bottom: 10px; border-radius: 4px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong>${backup.date}</strong><br>
                                    ${updateText.version}: ${backup.version} | <span class="badge ${branchClass}">${backup.branch}</span> | ${updateText.size}: ${backup.size}
                                </div>
                                <button class="btn btn-secondary btn-small" onclick="restoreBackup('${backup.name}'); closeModal('restoreModal');">
                                    ${updateText.restore}
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                restoreList.innerHTML = html;
            } else {
                restoreList.innerHTML = `<p class="text-muted">${updateText.noBackupsAvailable}</p>`;
            }
        });
}

// Populate version select
function populateVersionSelect(versions) {
    const select = document.getElementById('downgrade-version');
    let html = `<option value="">${updateText.selectVersion}</option>`;
    versions.forEach(version => {
        if (version !== '<?= ADMIN_PANEL_VERSION ?>') {
            html += `<option value="${version}">${version}</option>`;
        }
    });
    select.innerHTML = html;
}

// Handle downgrade form
document.getElementById('downgrade-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const version = document.getElementById('downgrade-version').value;
    if (!version) {
        alert(updateText.pleaseSelectVersion);
        return;
    }
    
    closeModal('downgradeModal');
    
    const formData = new FormData();
    formData.append('version', version);
    formData.append('csrf_token', adminCsrfToken);
    
    const log = document.getElementById('log');
    log.textContent = `${updateText.downgradingTo} ${version}...\n`;
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/update.php');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onprogress = function() {
        log.textContent = xhr.responseText;
        log.scrollTop = log.scrollHeight;
    };
    xhr.onload = function() {
        log.textContent = xhr.responseText;
        log.scrollTop = log.scrollHeight;
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    };
    xhr.send(formData);
});

// Load backups on page load
loadBackups();
checkUpdateStatus();

</script>
</body>
</html>
