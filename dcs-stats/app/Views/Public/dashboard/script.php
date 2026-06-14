<script>
window.DCS_DASHBOARD_CONFIG = {
    i18n: <?= json_encode([
        'unknown' => dcs_t('home.unknown'),
        'noData' => dcs_t('home.no_data'),
        'kills' => dcs_t('home.kills'),
        'deaths' => dcs_t('home.deaths'),
        'kdRatio' => dcs_t('home.kill_death_ratio'),
        'pvpKdRatio' => dcs_t('home.pvp_kill_death_ratio'),
        'pilotNames' => dcs_t('home.pilot_names'),
        'numberOfKills' => dcs_t('home.number_of_kills'),
        'numberOfKDRatio' => dcs_t('home.number_of_kd_ratio'),
        'numberOfPvpKDRatio' => dcs_t('home.number_of_pvp_kd_ratio'),
        'combatResults' => dcs_t('home.combat_results'),
        'count' => dcs_t('home.count'),
        'squadrons' => dcs_t('home.squadrons'),
        'performanceScore' => dcs_t('home.performance_score'),
        'hours' => dcs_t('home.hours'),
        'pilots' => dcs_t('home.pilots'),
        'players' => dcs_t('home.players'),
        'numberOfPlayers' => dcs_t('home.number_of_players'),
        'totalKills' => dcs_t('home.total_kills'),
        'totalDeaths' => dcs_t('home.total_deaths'),
        'totalCredits' => dcs_t('home.total_credits'),
        'squadronCredits' => dcs_t('home.squadron_credits'),
        'squadronNames' => dcs_t('home.squadron_names'),
        'dailyPlayers' => dcs_t('home.daily_players'),
        'date' => dcs_t('home.date')
    ], JSON_UNESCAPED_UNICODE) ?>,
    chartTheme: <?= json_encode($homepageChartTheme) ?>,
    publicDateFormat: <?= json_encode(dcs_public_date_format()) ?>,
    dataNeeds: <?= json_encode([
        'loadServerStats' => $showCoreServerStats,
        'loadAttendance' => $showAttendanceCards || $showTopApiLists,
        'loadTopPilots' => $showTopPilotsChart,
        'loadSquadrons' => $showTopSquadronsChart
    ]) ?>,
    features: <?= json_encode([
        'serverStats' => isFeatureEnabled('home_server_stats'),
        'topPilots' => $showTopPilotsChart,
        'missionStats' => isFeatureEnabled('home_mission_stats'),
        'topSquadrons' => $showTopSquadronsChart,
        'playerActivity' => isFeatureEnabled('home_player_activity'),
        'apiInsights' => $showAttendanceCards || $showTopApiLists
    ]) ?>
};
</script>
<script src="<?php echo htmlspecialchars(assetUrl('js/pages/dashboard.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
