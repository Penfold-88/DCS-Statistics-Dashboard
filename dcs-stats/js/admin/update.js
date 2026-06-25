// Check for updates on page load
let updateAvailable = false;
let latestVersion = null;
const adminUpdateConfig = window.DCS_ADMIN_UPDATE_CONFIG || {};
const adminCsrfToken = adminUpdateConfig.csrfToken || '';
const demoRestricted = Boolean(adminUpdateConfig.demoRestricted);
const demoRestrictionMessage = adminUpdateConfig.demoRestrictionMessage || '';
const adminCurrentVersion = adminUpdateConfig.currentVersion || '';
const updateText = adminUpdateConfig.i18n || {};

function replaceWithText(element, text, className = '') {
    if (!element) return;
    element.replaceChildren();
    const span = document.createElement('span');
    if (className) span.className = className;
    span.textContent = text || '';
    element.appendChild(span);
}

function replaceWithParagraph(element, text, className = '') {
    if (!element) return;
    element.replaceChildren();
    const paragraph = document.createElement('p');
    if (className) paragraph.className = className;
    paragraph.textContent = text || '';
    element.appendChild(paragraph);
}

function appendUpdateAction(element, label) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-primary btn-small';
    button.style.marginLeft = '10px';
    button.textContent = label || '';
    button.addEventListener('click', performUpdate);
    element.appendChild(document.createTextNode(' '));
    element.appendChild(button);
}

function textCell(text) {
    const cell = document.createElement('td');
    cell.textContent = text || '';
    return cell;
}

function badge(text, className) {
    const span = document.createElement('span');
    span.className = `badge ${className}`;
    span.textContent = text || '';
    return span;
}

function backupName(backup) {
    return String(backup && backup.name ? backup.name : '');
}

function backupBranchClass(branch) {
    return branch === 'Dev' ? 'badge-warning' : 'badge-primary';
}

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
                replaceWithText(statusDiv, updateText.sourceUnverifiedDetail, 'text-warning');
                appendUpdateAction(statusDiv, updateText.installLatest);
            } else if (data.includes('✅ Update Available!')) {
                updateAvailable = true;
                // Extract version from response
                const versionMatch = data.match(/Latest Release: (v?[\d.]+)/);
                if (versionMatch) {
                    latestVersion = versionMatch[1];
                }
                statusTitle.textContent = updateText.updateReady;
                replaceWithText(statusDiv, `${updateText.latestCodeAvailable} ${sourceLabel}.`);
                appendUpdateAction(statusDiv, updateText.updateNow);
            } else if (data.includes('✓ You are running the latest')) {
                statusTitle.textContent = updateText.upToDate;
                replaceWithText(statusDiv, updateText.upToDateDetail, 'text-success');
            } else if (data.includes('Could not fetch branch information')) {
                statusTitle.textContent = updateText.githubFailed;
                replaceWithText(statusDiv, updateText.githubFailedDetail);
            } else {
                statusTitle.textContent = updateText.unknownStatus;
                replaceWithText(statusDiv, updateText.unknownStatusDetail);
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
            replaceWithParagraph(document.getElementById('update-status'), updateText.failedCheck, 'text-danger');
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
                backupList.replaceChildren();
                const wrapper = document.createElement('div');
                wrapper.className = 'data-table-wrapper';
                const table = document.createElement('table');
                table.className = 'data-table';
                const thead = document.createElement('thead');
                const headRow = document.createElement('tr');
                [updateText.backupDate, updateText.version, updateText.branch, updateText.size, updateText.status, updateText.actions].forEach(label => {
                    const th = document.createElement('th');
                    th.textContent = label || '';
                    headRow.appendChild(th);
                });
                thead.appendChild(headRow);
                const tbody = document.createElement('tbody');
                data.backups.forEach((backup, index) => {
                    const branch = String(backup.branch || '');
                    const branchClass = backupBranchClass(branch);
                    const isProtected = index < 5;
                    const row = document.createElement('tr');
                    row.appendChild(textCell(String(backup.date || '')));
                    row.appendChild(textCell(String(backup.version || '')));
                    const branchCell = document.createElement('td');
                    branchCell.appendChild(badge(branch, branchClass));
                    row.appendChild(branchCell);
                    row.appendChild(textCell(String(backup.size || '')));
                    const statusCell = document.createElement('td');
                    statusCell.appendChild(isProtected ? badge(updateText.protected, 'badge-success') : badge(updateText.autoDeleted, 'badge-warning'));
                    row.appendChild(statusCell);
                    const actions = document.createElement('td');
                    const restore = document.createElement('button');
                    restore.type = 'button';
                    restore.className = 'btn btn-small btn-secondary';
                    restore.textContent = updateText.restore || '';
                    restore.addEventListener('click', () => restoreBackup(backupName(backup)));
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'btn btn-small btn-danger';
                    remove.textContent = updateText.delete || '';
                    remove.addEventListener('click', () => deleteBackup(backupName(backup)));
                    actions.append(restore, document.createTextNode(' '), remove);
                    row.appendChild(actions);
                    tbody.appendChild(row);
                });
                table.append(thead, tbody);
                wrapper.appendChild(table);
                const note = document.createElement('p');
                note.className = 'text-muted mt-2';
                note.textContent = updateText.keepsBackups || '';
                backupList.append(wrapper, note);
            } else {
                replaceWithParagraph(backupList, updateText.noBackups, 'text-muted');
            }
        })
        .catch(error => {
            replaceWithParagraph(document.getElementById('backup-list'), updateText.failedLoadBackups, 'text-danger');
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
                restoreList.replaceChildren();
                const list = document.createElement('div');
                list.className = 'backup-list';
                data.backups.forEach(backup => {
                    const branch = String(backup.branch || '');
                    const branchClass = backupBranchClass(branch);
                    const item = document.createElement('div');
                    item.className = 'backup-item';
                    item.style.padding = '10px';
                    item.style.border = '1px solid var(--border-color)';
                    item.style.marginBottom = '10px';
                    item.style.borderRadius = '4px';
                    const row = document.createElement('div');
                    row.style.display = 'flex';
                    row.style.justifyContent = 'space-between';
                    row.style.alignItems = 'center';
                    const details = document.createElement('div');
                    const date = document.createElement('strong');
                    date.textContent = String(backup.date || '');
                    details.append(date, document.createElement('br'), document.createTextNode(`${updateText.version}: ${backup.version || ''} | `), badge(branch, branchClass), document.createTextNode(` | ${updateText.size}: ${backup.size || ''}`));
                    const restore = document.createElement('button');
                    restore.type = 'button';
                    restore.className = 'btn btn-secondary btn-small';
                    restore.textContent = updateText.restore || '';
                    restore.addEventListener('click', () => {
                        restoreBackup(backupName(backup));
                        closeModal('restoreModal');
                    });
                    row.append(details, restore);
                    item.appendChild(row);
                    list.appendChild(item);
                });
                restoreList.appendChild(list);
            } else {
                replaceWithParagraph(restoreList, updateText.noBackupsAvailable, 'text-muted');
            }
        });
}

// Populate version select
function populateVersionSelect(versions) {
    const select = document.getElementById('downgrade-version');
    select.replaceChildren();
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = updateText.selectVersion || '';
    select.appendChild(placeholder);
    versions.forEach(version => {
        if (version !== adminCurrentVersion) {
            const option = document.createElement('option');
            option.value = version;
            option.textContent = version;
            select.appendChild(option);
        }
    });
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

