<main>
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars(dcs_t('credits.title')); ?></h1>
        <p class="dashboard-subtitle"><?php echo htmlspecialchars(dcs_t('credits.subtitle')); ?></p>
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
        <h3 style="text-align: center; color: #ccc; margin-bottom: 20px;"><?php echo htmlspecialchars(dcs_t('credits.search_results')); ?></h3>
        <div id="results-list" class="results-list"></div>
    </div>

    <div id="loading" class="loading-spinner" style="display: none;">
        <p><?php echo htmlspecialchars(dcs_t('credits.searching')); ?></p>
    </div>

    <div id="credits-display" style="display: none;">
        <div id="pilot-card" class="pilot-card">
            <h3 id="pilot-display-name"></h3>
            <div class="pilot-stats">
                <div class="stat-group">
                    <h4><?php echo htmlspecialchars(dcs_t('credits.information')); ?></h4>
                    <div class="stats-grid">
                        <div class="stat-item credits-stat-item">
                            <span class="stat-label"><?php echo htmlspecialchars(dcs_t('credits.current_balance')); ?>:</span>
                            <span class="stat-value credits-value" id="credits-value">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="search-again">
                <button onclick="searchAgain()" class="search-container button"><?php echo htmlspecialchars(dcs_t('credits.search_another')); ?></button>
            </div>
        </div>
    </div>

    <div id="no-results" class="no-results" style="display: none;">
        <p id="no-results-message"><?php echo htmlspecialchars(dcs_t('credits.no_data')); ?></p>
        <button onclick="searchAgain()" class="btn-secondary"><?php echo htmlspecialchars(dcs_t('credits.try_another')); ?></button>
    </div>
</main>
