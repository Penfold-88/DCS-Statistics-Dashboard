<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex,nofollow,noarchive">
    <title><?= e(dcs_t('admin.cms.widget_' . str_replace('-', '_', $widgetType))) ?></title>
    <link rel="stylesheet" href="<?= e(assetUrl('styles.php')) ?>"><link rel="stylesheet" href="<?= e(assetUrl('css/widgets/dashboard-widgets.css')) ?>">
    <style>html,body{background:transparent!important;margin:0;min-height:0}body{padding:8px}.cms-dashboard-widget{margin:0}</style>
    <script>window.DCS_CONFIG=<?= getJsConfig() ?>;window.DCS_DASHBOARD_WIDGET_TEXT=<?= json_encode([
        'unavailable'=>dcs_t('widget.dashboard.unavailable'),'noData'=>dcs_t('home.no_data'),'summary'=>dcs_t('admin.cms.widget_summary'),'attendance'=>dcs_t('admin.cms.widget_attendance'),'topPilots'=>dcs_t('admin.cms.widget_top_pilots'),'combatStats'=>dcs_t('admin.cms.widget_combat_stats'),'topSquadrons'=>dcs_t('admin.cms.widget_top_squadrons'),'playerActivity'=>dcs_t('admin.cms.widget_player_activity'),'topTheatres'=>dcs_t('admin.cms.widget_top_theatres'),'topMissions'=>dcs_t('admin.cms.widget_top_missions'),'topModules'=>dcs_t('admin.cms.widget_top_modules'),'totalPlayers'=>dcs_t('home.total_players'),'totalPlaytime'=>dcs_t('home.total_playtime'),'averagePlaytime'=>dcs_t('home.average_playtime'),'totalSorties'=>dcs_t('home.total_sorties'),'players24h'=>dcs_t('home.players_24h'),'players7d'=>dcs_t('home.players_7d'),'players30d'=>dcs_t('home.players_30d'),'currentPlayers'=>dcs_t('home.current_players'),'kills'=>dcs_t('home.kills'),'deaths'=>dcs_t('home.deaths'),'kdRatio'=>dcs_t('home.kill_death_ratio'),'pvpKdRatio'=>dcs_t('home.pvp_kill_death_ratio'),'hours'=>dcs_t('home.hours'),'pilots'=>dcs_t('home.pilots'),'players'=>dcs_t('home.players'),'credits'=>dcs_t('home.total_credits')
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="<?= e(assetUrl('js/api-client.js')) ?>" defer></script><script src="<?= e(assetUrl('js/widgets/dashboard-widgets.js')) ?>" defer></script>
</head>
<body><section class="cms-dashboard-widget" data-dashboard-widget="<?= e($widgetType) ?>" data-server-filter="<?= e($serverFilter) ?>" data-metric="<?= e($metric) ?>" data-limit="<?= (int)$limit ?>" aria-live="polite"><p class="cms-widget-message"><?= e(dcs_t('widget.dashboard.loading')) ?></p></section></body>
</html>
