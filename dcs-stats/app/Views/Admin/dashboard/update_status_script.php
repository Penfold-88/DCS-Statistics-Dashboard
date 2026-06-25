<script>
window.DCS_ADMIN_DASHBOARD_UPDATE_CONFIG = {
    i18n: <?= json_encode([
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
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="../js/admin/dashboard-update-status.js"></script>
