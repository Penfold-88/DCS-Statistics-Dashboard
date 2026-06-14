<script>
window.DCS_LEADERBOARD_CONFIG = {
    chartTheme: <?php echo json_encode($chartTheme); ?>,
    needsPlayerDetails: <?php echo json_encode(
        isFeatureEnabled('leaderboard_chart') ||
        isFeatureEnabled('leaderboard_sorties') ||
        isFeatureEnabled('leaderboard_takeoffs') ||
        isFeatureEnabled('leaderboard_landings') ||
        isFeatureEnabled('leaderboard_crashes') ||
        isFeatureEnabled('leaderboard_ejections') ||
        isFeatureEnabled('leaderboard_aircraft')
    ); ?>,
    features: <?php echo json_encode([
        'kills' => isFeatureEnabled('leaderboard_kills'),
        'deaths' => isFeatureEnabled('leaderboard_deaths'),
        'kdRatio' => isFeatureEnabled('leaderboard_kd_ratio'),
        'pvpKdRatio' => isFeatureEnabled('leaderboard_pvp_kd_ratio'),
        'credits' => isFeatureEnabled('leaderboard_credits'),
        'playtime' => isFeatureEnabled('leaderboard_playtime'),
        'sorties' => isFeatureEnabled('leaderboard_sorties'),
        'takeoffs' => isFeatureEnabled('leaderboard_takeoffs'),
        'landings' => isFeatureEnabled('leaderboard_landings'),
        'crashes' => isFeatureEnabled('leaderboard_crashes'),
        'ejections' => isFeatureEnabled('leaderboard_ejections'),
        'aircraft' => isFeatureEnabled('leaderboard_aircraft')
    ]); ?>,
    i18n: <?php echo json_encode([
        'kills' => dcs_t('leaderboard.kills'),
        'deaths' => dcs_t('leaderboard.deaths'),
        'kd' => dcs_t('leaderboard.kd'),
        'pvpKd' => dcs_t('leaderboard.pvp_kd'),
        'credits' => dcs_t('leaderboard.credits'),
        'playtimeHours' => dcs_t('leaderboard.playtime_hours'),
        'takeoffs' => dcs_t('leaderboard.takeoffs'),
        'landings' => dcs_t('leaderboard.landings'),
        'crashes' => dcs_t('leaderboard.crashes'),
        'ejections' => dcs_t('leaderboard.ejections'),
        'unknown' => dcs_t('leaderboard.unknown'),
        'loading' => dcs_t('leaderboard.loading'),
        'loadError' => dcs_t('leaderboard.load_error')
    ], JSON_UNESCAPED_UNICODE); ?>
};
</script>
<script src="<?php echo htmlspecialchars(assetUrl('js/pages/leaderboard.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
