<script>
window.DCS_SQUADRONS_CONFIG = {
    i18n: <?php echo json_encode([
        'members' => dcs_t('squadrons.members'),
        'clickToggle' => dcs_t('squadrons.click_toggle'),
        'lastSeen' => dcs_t('squadrons.last_seen'),
        'unknown' => dcs_t('squadrons.unknown'),
        'credits' => dcs_t('squadrons.credits'),
        'loadFailed' => dcs_t('squadrons.load_failed'),
        'errorTitle' => dcs_t('squadrons.error_title'),
        'configRetry' => dcs_t('squadrons.config_retry'),
        'apiUnavailable' => 'API Currently Unavailable'
    ], JSON_UNESCAPED_UNICODE); ?>
};
</script>
<script src="<?php echo htmlspecialchars(assetUrl('js/pages/squadrons.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
