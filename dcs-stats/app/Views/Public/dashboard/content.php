<main>
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars(dcs_t('home.title')); ?></h1>
        <p class="dashboard-subtitle"><?php echo htmlspecialchars(dcs_t('home.subtitle')); ?></p>
    </div>

    <div class="dashboard-api-unavailable" id="dashboardApiUnavailable" style="display: none;" role="status" aria-live="polite">
        <strong id="dashboardApiUnavailableText">API Currently Unavailable</strong>
    </div>

    <?php if (isFeatureEnabled('home_server_stats')): ?>
    <div class="stats-cards">
        <div class="stat-card" id="totalPlayersCard">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo htmlspecialchars(dcs_t('home.total_players')); ?></h3>
                <p class="stat-number" id="totalPlayers">-</p>
            </div>
        </div>

        <div class="stat-card" id="totalPlaytimeCard">
            <div class="stat-icon">✈️</div>
            <div class="stat-content">
                <h3><?php echo htmlspecialchars(dcs_t('home.total_playtime')); ?></h3>
                <p class="stat-number" id="totalPlaytime">-</p>
            </div>
        </div>

        <div class="stat-card" id="avgPlaytimeCard">
            <div class="stat-icon">🕐</div>
            <div class="stat-content">
                <h3><?php echo htmlspecialchars(dcs_t('home.average_playtime')); ?></h3>
                <p class="stat-number" id="avgPlaytime">-</p>
            </div>
        </div>

        <div class="stat-card" id="totalSortiesCard">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3><?php echo htmlspecialchars(dcs_t('home.total_sorties')); ?></h3>
                <p class="stat-number" id="totalSorties">-</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($showAttendanceCards): ?>
    <div class="api-attendance" id="apiAttendance" style="display: none;">
        <div class="insight-cards">
            <?php if (isFeatureEnabled('home_api_players_24h')): ?>
            <div class="insight-card">
                <div class="insight-icon">👥</div>
                <div class="insight-content">
                    <span><?php echo htmlspecialchars(dcs_t('home.players_24h')); ?></span>
                    <strong id="players24h">-</strong>
                </div>
            </div>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_api_players_7d')): ?>
            <div class="insight-card">
                <div class="insight-icon">👥</div>
                <div class="insight-content">
                    <span><?php echo htmlspecialchars(dcs_t('home.players_7d')); ?></span>
                    <strong id="players7d">-</strong>
                </div>
            </div>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_api_players_30d')): ?>
            <div class="insight-card">
                <div class="insight-icon">👥</div>
                <div class="insight-content">
                    <span><?php echo htmlspecialchars(dcs_t('home.players_30d')); ?></span>
                    <strong id="players30d">-</strong>
                </div>
            </div>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_api_current_players')): ?>
            <div class="insight-card">
                <div class="insight-icon">👥</div>
                <div class="insight-content">
                    <span><?php echo htmlspecialchars(dcs_t('home.current_players')); ?></span>
                    <strong id="currentPlayers">-</strong>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="charts-dashboard">
        <?php if (isFeatureEnabled('home_top_pilots')): ?>
        <div class="chart-container" title="<?php echo htmlspecialchars(dcs_t('home.chart_top_pilots_title')); ?>">
            <div class="chart-card-header">
                <h2><?php echo htmlspecialchars(dcs_t('home.chart_top_pilots')); ?> <span class="chart-info">ⓘ</span></h2>
                <label class="chart-metric-control" for="topPilotsMetric">
                    <span class="chart-metric-select">
                        <select id="topPilotsMetric">
                            <option value="kills"><?php echo htmlspecialchars(dcs_t('home.kills')); ?></option>
                            <option value="kdr"><?php echo htmlspecialchars(dcs_t('home.kill_death_ratio')); ?></option>
                            <option value="kdr_pvp"><?php echo htmlspecialchars(dcs_t('home.pvp_kill_death_ratio')); ?></option>
                        </select>
                    </span>
                </label>
            </div>
            <canvas id="topPilotsChart"></canvas>
            <p class="no-data-message" id="topPilotsNoData" style="display: none;"><?php echo htmlspecialchars(dcs_t('home.no_mission_data')); ?></p>
        </div>
        <?php endif; ?>

        <?php if (isFeatureEnabled('home_mission_stats')): ?>
        <div class="chart-container" title="<?php echo htmlspecialchars(dcs_t('home.chart_combat_stats_title')); ?>">
            <h2><?php echo htmlspecialchars(dcs_t('home.chart_combat_stats')); ?> <span class="chart-info">ⓘ</span></h2>
            <canvas id="combatStatsChart"></canvas>
        </div>
        <?php endif; ?>

        <?php if (isFeatureEnabled('squadrons_enabled') && isFeatureEnabled('home_top_pilots')): ?>
        <div class="chart-container" title="<?php echo htmlspecialchars(dcs_t('home.chart_top_squadrons_title')); ?>">
            <h2><?php echo htmlspecialchars(dcs_t('home.chart_top_squadrons')); ?> <span class="chart-info">ⓘ</span></h2>
            <canvas id="topSquadronsChart"></canvas>
            <p class="no-data-message" id="squadronsNoData" style="display: none;"><?php echo htmlspecialchars(dcs_t('home.no_squadron_data')); ?></p>
        </div>
        <?php endif; ?>

        <?php if (isFeatureEnabled('home_player_activity')): ?>
        <div class="chart-container full-width" title="<?php echo htmlspecialchars(dcs_t('home.chart_activity_title')); ?>">
            <h2><?php echo htmlspecialchars(dcs_t('home.chart_activity')); ?> <span class="chart-info">ⓘ</span></h2>
            <canvas id="playerActivityChart"></canvas>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($showTopApiLists): ?>
    <div class="api-insights" id="apiInsights" style="display: none;">
        <div class="insight-grid">
            <?php if (isFeatureEnabled('home_top_theatres')): ?>
            <section class="insight-panel">
                <h3><?php echo htmlspecialchars(dcs_t('home.top_theatres')); ?></h3>
                <div id="topTheatresList" class="rank-list"></div>
            </section>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_top_missions')): ?>
            <section class="insight-panel">
                <h3><?php echo htmlspecialchars(dcs_t('home.top_missions')); ?></h3>
                <div id="topMissionsList" class="rank-list"></div>
            </section>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_top_modules')): ?>
            <section class="insight-panel">
                <h3><?php echo htmlspecialchars(dcs_t('home.top_modules')); ?></h3>
                <div id="topModulesList" class="rank-list"></div>
            </section>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div id="loading-overlay" class="loading-overlay">
        <div class="loader"></div>
        <p><?php echo htmlspecialchars(dcs_t('home.loading_stats')); ?></p>
    </div>
</main>
