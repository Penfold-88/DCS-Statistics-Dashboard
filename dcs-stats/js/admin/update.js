// Check for updates on page load
let updateAvailable = false;
let latestVersion = null;
const adminUpdateConfig = window.DCS_ADMIN_UPDATE_CONFIG || {};
const adminCsrfToken = adminUpdateConfig.csrfToken || '';
const demoRestricted = Boolean(adminUpdateConfig.demoRestricted);
const demoRestrictionMessage = adminUpdateConfig.demoRestrictionMessage || '';
const adminCurrentVersion = adminUpdateConfig.currentVersion || '';
const updateText = adminUpdateConfig.i18n || {};

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
            if (data.includes('⚠ Installed source commit is unverified.')) {
                statusTitle.textContent = updateText.sourceUnverified;
                statusDiv.innerHTML = `<span class="text-warning">${updateText.sourceUnverifiedDetail}</span> <button class="btn btn-primary btn-small" onclick="performUpdate()" style="margin-left: 10px;">${updateText.installLatest}</button>`;
            } else if (data.includes('✅ Update Available!')) {
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
    if (demoRestricted) {
        alert(demoRestrictionMessage);
        return;
    }
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
    if (demoRestricted) {
        alert(demoRestrictionMessage);
        return;
    }
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
    if (demoRestricted) {
        alert(demoRestrictionMessage);
        return;
    }
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
    if (demoRestricted) {
        alert(demoRestrictionMessage);
        return;
    }
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
    if (demoRestricted) {
        alert(demoRestrictionMessage);
        return;
    }
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
    if (demoRestricted) {
        alert(demoRestrictionMessage);
        return;
    }
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
        if (version !== adminCurrentVersion) {
            html += `<option value="${version}">${version}</option>`;
        }
    });
    select.innerHTML = html;
}

// Handle downgrade form
document.getElementById('downgrade-form').addEventListener('submit', function(e) {
    e.preventDefault();
    if (demoRestricted) {
        alert(demoRestrictionMessage);
        return;
    }
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

