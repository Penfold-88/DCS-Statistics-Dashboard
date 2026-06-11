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

    setInterval(() => {
        // In a real implementation, this would fetch new activity via AJAX
    }, 30000);
</script>
