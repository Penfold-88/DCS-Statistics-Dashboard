<main>
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars(dcs_t('squadrons.title')); ?></h1>
        <p class="dashboard-subtitle"><?php echo htmlspecialchars(dcs_t('squadrons.subtitle')); ?></p>
    </div>

    <div class="search-container">
        <input type="text" id="searchInput" placeholder="<?php echo htmlspecialchars(dcs_t('squadrons.search_placeholder')); ?>">
    </div>

    <div class="table-wrapper">
        <table id="squadronsTable">
            <thead>
                <tr>
                    <th><?php echo htmlspecialchars(dcs_t('squadrons.logo')); ?></th>
                    <th><?php echo htmlspecialchars(dcs_t('squadrons.name')); ?></th>
                    <th><?php echo htmlspecialchars(dcs_t('squadrons.description')); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Mobile Cards Container -->
    <div class="mobile-cards" id="squadronsCards"></div>

    <?php if (isFeatureEnabled('squadron_management')): ?>
    <h2><?php echo htmlspecialchars(dcs_t('squadrons.members_title')); ?></h2>
    <div class="table-wrapper">
        <table id="membersTable">
            <thead>
                <tr>
                    <th><?php echo htmlspecialchars(dcs_t('squadrons.logo')); ?></th>
                    <th><?php echo htmlspecialchars(dcs_t('squadrons.squadron_name')); ?></th>
                    <th><?php echo htmlspecialchars(dcs_t('squadrons.member')); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Mobile Cards Container -->
    <div class="mobile-cards" id="membersCards"></div>
    <?php endif; ?>

    <?php if (isFeatureEnabled('squadron_statistics') && isFeatureEnabled('credits_enabled')): ?>
    <h2><?php echo htmlspecialchars(dcs_t('squadrons.leaderboard_title')); ?></h2>
    <div class="table-wrapper">
        <table id="leaderboardTable">
            <thead>
                <tr>
                    <th><?php echo htmlspecialchars(dcs_t('squadrons.logo')); ?></th>
                    <th><?php echo htmlspecialchars(dcs_t('squadrons.squadron_name')); ?></th>
                    <th><?php echo htmlspecialchars(dcs_t('squadrons.credits')); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Mobile Cards Container -->
    <div class="mobile-cards" id="leaderboardCards"></div>
    <?php endif; ?>

    <div id="error-message" style="color: red; text-align: center;"></div>
</main>
