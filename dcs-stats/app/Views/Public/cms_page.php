<?php include DCS_APP_PATH . '/Views/Layout/header.php'; ?>
<?php include DCS_APP_PATH . '/Views/Layout/nav.php'; ?>

<?php if (!empty($isPreview)): ?><div class="cms-preview-banner"><?= e(dcs_t('admin.cms.preview_banner')) ?></div><?php endif; ?>

<main>
    <div class="dashboard-header">
        <h1><?= e($page['title'] ?? dcs_t('cms.not_found')) ?></h1>
    </div>
    <section class="cms-public-page">
        <?php if ($page): ?>
            <?= $contentHtml ?>
        <?php else: ?>
            <p><?= e(dcs_t('cms.not_found_message')) ?></p>
        <?php endif; ?>
    </section>
</main>

<style>
.cms-public-page { background: var(--card_color, rgba(20,40,60,.88)); border: 1px solid var(--border_color, rgba(255,255,255,.15)); border-radius: 16px; box-sizing: border-box; line-height: 1.75; margin: 20px auto 40px; max-width: 1100px; min-width: 0; overflow: hidden; padding: clamp(22px, 4vw, 48px); white-space: normal; width: calc(100% - 24px); }
.cms-public-page * { box-sizing: border-box; min-width: 0; }
.cms-public-page > *, .cms-public-page [data-server-status-widget], .cms-public-page [data-dashboard-widget], .cms-public-page [data-gallery-carousel] { max-width: 100% !important; }
.cms-public-page h2, .cms-public-page h3, .cms-public-page h4 { margin: 1.35em 0 .45em; }
.cms-public-page p { margin: 0 0 1em; }
.cms-public-page ul, .cms-public-page ol { margin: 0 0 1em 1.6em; }
.cms-public-page blockquote { border-left: 4px solid var(--accent_color, #4caf50); color: var(--text_muted, #aaa); margin: 1em 0; padding: .5em 1em; }
.cms-public-page a { color: var(--link_color, #64b5f6); text-decoration: underline; }
.cms-public-page .cms-text-left { text-align: left; }
.cms-public-page .cms-text-center { text-align: center; }
.cms-public-page .cms-text-right { text-align: right; }
.cms-public-page figure { box-sizing: border-box; clear: both; margin: 1.5em auto; max-width: 100%; }
.cms-public-page figure[data-image-size="25"] { width: 25%; }
.cms-public-page figure[data-image-size="50"] { width: 50%; }
.cms-public-page figure[data-image-size="75"] { width: 75%; }
.cms-public-page figure[data-image-size="100"] { width: 100%; }
.cms-public-page figure[data-image-size] img { width: 100%; }
.cms-public-page figure img { border-radius: 10px; display: block; height: auto; max-width: 100% !important; object-fit: contain; }
.cms-public-page figcaption { color: var(--text_muted, #aaa); font-size: .9em; margin-top: .55em; text-align: center; }
.cms-public-page .cms-image-center { width: fit-content; }
.cms-public-page .cms-image-left { float: left; margin: .5em 1.5em 1em 0; max-width: 48%; }
.cms-public-page .cms-image-right { float: right; margin: .5em 0 1em 1.5em; max-width: 48%; }
.cms-public-page .cms-image-left[data-image-size], .cms-public-page .cms-image-right[data-image-size] { max-width: 100%; }
.cms-public-page .cms-image-wide { width: 100%; }
.cms-public-page .cms-image-wide img { width: 100%; }
@media (max-width: 700px) {
    .cms-public-page { padding: 22px; }
    .cms-public-page .cms-image-left, .cms-public-page .cms-image-center, .cms-public-page .cms-image-right, .cms-public-page .cms-image-wide { float: none; margin: 1.5em auto; max-width: 100%; width: 100%; }
    .cms-public-page figure img { max-width: 100% !important; width: 100%; }
    .cms-public-page [data-server-status-widget], .cms-public-page [data-dashboard-widget], .cms-public-page [data-gallery-carousel] { width: 100%; }
}
.cms-preview-banner { background: #ff9800; color: #111; font-weight: 700; padding: 10px; text-align: center; }
</style>

<?php if (!empty($hasServerStatusWidget)): ?>
<link rel="stylesheet" href="<?= e(assetUrl('css/widgets/server-status.css')) ?>">
<script>window.DCS_SERVER_STATUS_WIDGET=<?= json_encode([
    'loading' => dcs_t('widget.server_status.loading'),
    'unavailable' => dcs_t('widget.server_status.unavailable'),
    'unknownServer' => dcs_t('servers.unknown_server'),
    'unknown' => dcs_t('servers.unknown'),
    'mission' => dcs_t('servers.mission'),
    'theatre' => dcs_t('servers.theatre'),
    'players' => dcs_t('widget.server_status.players'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= e(assetUrl('js/widgets/server-status.js')) ?>"></script>
<?php endif; ?>

<?php if (!empty($hasImageGallery)): ?>
<link rel="stylesheet" href="<?= e(assetUrl('css/widgets/image-gallery.css')) ?>">
<script>window.DCS_GALLERY_TEXT=<?= json_encode(['close'=>dcs_t('cms.gallery.close')], JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= e(assetUrl('js/widgets/image-gallery.js')) ?>"></script>
<?php endif; ?>

<?php if (!empty($hasDashboardWidgets)): ?>
<link rel="stylesheet" href="<?= e(assetUrl('css/widgets/dashboard-widgets.css')) ?>">
<script>window.DCS_DASHBOARD_WIDGET_TEXT=<?= json_encode([
    'unavailable'=>dcs_t('widget.dashboard.unavailable'),'noData'=>dcs_t('home.no_data'),'allServers'=>dcs_t('admin.cms.all_servers'),
    'summary'=>dcs_t('admin.cms.widget_summary'),'attendance'=>dcs_t('admin.cms.widget_attendance'),'topPilots'=>dcs_t('admin.cms.widget_top_pilots'),'combatStats'=>dcs_t('admin.cms.widget_combat_stats'),'topSquadrons'=>dcs_t('admin.cms.widget_top_squadrons'),'playerActivity'=>dcs_t('admin.cms.widget_player_activity'),'topTheatres'=>dcs_t('admin.cms.widget_top_theatres'),'topMissions'=>dcs_t('admin.cms.widget_top_missions'),'topModules'=>dcs_t('admin.cms.widget_top_modules'),
    'totalPlayers'=>dcs_t('home.total_players'),'totalPlaytime'=>dcs_t('home.total_playtime'),'averagePlaytime'=>dcs_t('home.average_playtime'),'totalSorties'=>dcs_t('home.total_sorties'),'players24h'=>dcs_t('home.players_24h'),'players7d'=>dcs_t('home.players_7d'),'players30d'=>dcs_t('home.players_30d'),'currentPlayers'=>dcs_t('home.current_players'),'kills'=>dcs_t('home.kills'),'deaths'=>dcs_t('home.deaths'),'kdRatio'=>dcs_t('home.kill_death_ratio'),'pvpKdRatio'=>dcs_t('home.pvp_kill_death_ratio'),'hours'=>dcs_t('home.hours'),'pilots'=>dcs_t('home.pilots'),'players'=>dcs_t('home.players'),'credits'=>dcs_t('home.total_credits')
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= e(assetUrl('js/widgets/dashboard-widgets.js')) ?>"></script>
<?php endif; ?>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
