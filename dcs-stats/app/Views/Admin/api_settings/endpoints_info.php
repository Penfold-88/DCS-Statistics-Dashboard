<div class="endpoints-info">
    <h4><?= e(dcs_t('admin.api.available_endpoints')) ?></h4>
    <div class="endpoints-list">
        <strong><?= e(dcs_t('admin.api.rest_endpoints')) ?>:</strong>
        <ul>
            <li><code>GET /servers</code> - <?= e(dcs_t('admin.api.endpoint_servers')) ?></li>
            <li><code>GET /serverstats</code> - <?= e(dcs_t('admin.api.endpoint_serverstats')) ?></li>
            <li><code>GET /server_attendance</code> - <?= e(dcs_t('admin.api.endpoint_attendance')) ?></li>
            <li><code>GET /leaderboard</code> - <?= e(dcs_t('admin.api.endpoint_leaderboard')) ?></li>
            <li><code>GET /highscore</code> - <?= e(dcs_t('admin.api.endpoint_highscore')) ?></li>
            <li><code>GET /trueskill</code> - <?= e(dcs_t('admin.api.endpoint_trueskill')) ?></li>
            <li><code>POST /player_info</code> - <?= e(dcs_t('admin.api.endpoint_player_info')) ?></li>
            <li><code>POST /stats</code> - <?= e(dcs_t('admin.api.endpoint_stats')) ?></li>
            <li><code>POST /getuser</code> - <?= e(dcs_t('admin.api.endpoint_getuser')) ?></li>
            <li><code>POST /credits</code> - <?= e(dcs_t('admin.api.endpoint_credits')) ?></li>
            <li><code>POST /modulestats</code> - <?= e(dcs_t('admin.api.endpoint_modulestats')) ?></li>
            <li><code>POST /traps</code> - <?= e(dcs_t('admin.api.endpoint_traps')) ?></li>
            <li><code>POST /weaponpk</code> - <?= e(dcs_t('admin.api.endpoint_weaponpk')) ?></li>
            <li><code>GET /squadrons</code> - <?= e(dcs_t('admin.api.endpoint_squadrons')) ?></li>
            <li><code>POST /player_squadrons</code> - <?= e(dcs_t('admin.api.endpoint_player_squadrons')) ?></li>
            <li><code>POST /squadron_members</code> - <?= e(dcs_t('admin.api.endpoint_squadron_members')) ?></li>
            <li><code>POST /squadron_credits</code> - <?= e(dcs_t('admin.api.endpoint_squadron_credits')) ?></li>
            <li><code>GET /current_server</code> - <?= e(dcs_t('admin.api.endpoint_current_server')) ?></li>
            <li><code>GET /airbases</code>, <code>GET /airbase</code>, <code>GET /airbase/atis</code>, <code>GET /airbase/warehouse</code> - <?= e(dcs_t('admin.api.endpoint_airbases')) ?></li>
            <li><code>GET /convertCoordinates</code> - <?= e(dcs_t('admin.api.endpoint_coordinates')) ?></li>
            <li><code>GET /mission/group/waypoints</code> - <?= e(dcs_t('admin.api.endpoint_waypoints')) ?></li>
        </ul>

        <strong><?= e(dcs_t('admin.api.dashboard_endpoints')) ?>:</strong>
        <ul>
            <li><code>get_servers.php</code> - <?= e(dcs_t('admin.api.dashboard_servers')) ?></li>
            <li><code>get_server_stats.php</code> - <?= e(dcs_t('admin.api.dashboard_server_stats')) ?></li>
            <li><code>get_leaderboard.php</code> - <?= e(dcs_t('admin.api.dashboard_leaderboard')) ?></li>
            <li><code>get_player_stats.php</code> - <?= e(dcs_t('admin.api.dashboard_player_stats')) ?></li>
            <li><code>get_credits.php</code> - <?= e(dcs_t('admin.api.dashboard_credits')) ?></li>
            <li><code>get_missionstats.php</code> - <?= e(dcs_t('admin.api.dashboard_missionstats')) ?></li>
            <li><code>get_squadrons.php</code> - <?= e(dcs_t('admin.api.dashboard_squadrons')) ?></li>
            <li><code>get_squadron_members.php</code> - <?= e(dcs_t('admin.api.dashboard_squadron_members')) ?></li>
            <li><code>get_squadron_credits.php</code> - <?= e(dcs_t('admin.api.dashboard_squadron_credits')) ?></li>
            <li><code>get_api_config.php</code> - <?= e(dcs_t('admin.api.dashboard_api_config')) ?></li>
            <li><code>get_leaderboard_client.php</code> - <?= e(dcs_t('admin.api.dashboard_leaderboard_client')) ?></li>
        </ul>

        <strong><?= e(dcs_t('admin.common.note')) ?>:</strong> <?= e(dcs_t('admin.api.expanded_note')) ?>
    </div>
</div>
