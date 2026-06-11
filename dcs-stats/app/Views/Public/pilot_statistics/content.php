<main class="pilot-statistics-page">
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars(dcs_t('pilot.title')); ?></h1>
        <p class="dashboard-subtitle"><?php echo htmlspecialchars(dcs_t('pilot.subtitle')); ?></p>
    </div>

    <?php if (isFeatureEnabled('pilot_search')): ?>
    <div class="search-container">
        <input type="text" id="playerSearchInput" placeholder="<?php echo htmlspecialchars(dcs_t('pilot.search_placeholder')); ?>" />
        <button onclick="searchForPlayers()"><?php echo htmlspecialchars(dcs_t('pilot.search_button')); ?></button>
    </div>
    <?php else: ?>
    <div class="alert" style="text-align: center; padding: 20px;">
        <p><?php echo htmlspecialchars(dcs_t('pilot.search_disabled')); ?></p>
    </div>
    <?php endif; ?>

    <div id="multiple-results" style="display: none;">
        <h3 style="text-align: center; color: #ccc;"><?php echo htmlspecialchars(dcs_t('pilot.multiple_found')); ?></h3>
        <div id="results-list" class="results-list"></div>
    </div>

    <div id="search-results" style="display: none;">
        <div id="pilot-card" class="pilot-card">
            <h3 id="pilot-name"></h3>
            <div class="pilot-stats">
                <div class="stat-group" id="combat-stats-group">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.combat_statistics')); ?></h4>
                    <div class="stats-grid" id="combat-stats-grid">
                        <!-- Combat stats will be dynamically added here -->
                    </div>
                </div>

                <div class="stat-group" id="secondary-stats-group" style="display: none;">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.additional_information')); ?></h4>
                    <div class="stats-grid" id="secondary-stats-grid">
                        <!-- Secondary stats will be dynamically added here -->
                    </div>
                </div>

                <div class="stat-group" id="session-stats-group" style="display: none;">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.last_session')); ?></h4>
                    <div class="stats-grid" id="session-stats-grid">
                        <!-- Session stats will be dynamically added here -->
                    </div>
                </div>
            </div>

            <div class="charts-container">
                <?php if (isFeatureEnabled('pilot_combat_stats')): ?>
                <div class="chart-wrapper" title="<?php echo htmlspecialchars(dcs_t('pilot.combat_performance_title')); ?>">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.combat_performance')); ?> <span class="chart-info">ⓘ</span></h4>
                    <canvas id="combatChart"></canvas>
                </div>
                <?php endif; ?>
                <?php if (isFeatureEnabled('pilot_flight_stats')): ?>
                <div class="chart-wrapper" title="<?php echo htmlspecialchars(dcs_t('pilot.flight_statistics_title')); ?>">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.flight_statistics')); ?> <span class="chart-info">ⓘ</span></h4>
                    <canvas id="flightChart"></canvas>
                </div>
                <?php endif; ?>
                <?php if (isFeatureEnabled('pilot_aircraft_chart')): ?>
                <div class="chart-wrapper" id="aircraftChartWrapper" style="display: none;" title="<?php echo htmlspecialchars(dcs_t('pilot.aircraft_usage_title')); ?>">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.aircraft_usage')); ?> <span class="chart-info">ⓘ</span></h4>
                    <canvas id="aircraftChart"></canvas>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isFeatureEnabled('pilot_carrier_traps')): ?>
        <div class="pilot-card carrier-traps-card" id="carrier-traps-group" style="display: none;">
            <h3><?php echo htmlspecialchars(dcs_t('pilot.carrier_landings')); ?></h3>
            <p class="carrier-traps-status" id="carrier-traps-status"></p>
            <div class="stats-grid" id="carrier-traps-summary"></div>
            <div class="carrier-traps-table-wrap" id="carrier-traps-table-wrap" style="display: none;">
                <table class="carrier-traps-table">
                    <thead>
                        <tr>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_grade')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_points')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_wire')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_aircraft')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_location')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_time')); ?></th>
                        </tr>
                    </thead>
                    <tbody id="carrier-traps-table-body"></tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div id="no-results" style="display: none; text-align: center; color: #ccc; margin-top: 30px;">
        <p id="no-results-message"><?php echo htmlspecialchars(dcs_t('pilot.no_pilot_found')); ?></p>
    </div>

    <div id="loading" style="display: none; text-align: center; color: #ccc; margin-top: 30px;">
        <p><?php echo htmlspecialchars(dcs_t('pilot.searching')); ?></p>
    </div>
</main>
