<main class="container leaderboard-page">
  <div class="dashboard-header">
    <h1><?php echo htmlspecialchars(dcs_t('leaderboard.title')); ?></h1>
    <p class="dashboard-subtitle"><?php echo htmlspecialchars(dcs_t('leaderboard.subtitle')); ?></p>
  </div>
  <div id="leaderboard-loading"><?php echo htmlspecialchars(dcs_t('leaderboard.loading')); ?></div>

  <div id="top3-wrapper">
    <div class="top-3-container" id="top3-leaderboard"></div>
  </div>

  <div class="table-responsive">
    <table id="leaderboardTable">
      <thead>
        <tr>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.rank')); ?></th>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.name')); ?></th>
          <?php if (isFeatureEnabled('leaderboard_kills')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.kills')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_deaths')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.deaths')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_kd_ratio')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.kd')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_pvp_kd_ratio')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.pvp_kd')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_credits')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.credits')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_playtime')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.playtime')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_sorties')): ?>
          <th class="col-sorties"><?php echo htmlspecialchars(dcs_t('leaderboard.sorties')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_takeoffs')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.takeoffs')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_landings')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.landings')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_crashes')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.crashes')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_ejections')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.ejections')); ?></th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_aircraft')): ?>
          <th><?php echo htmlspecialchars(dcs_t('leaderboard.aircraft')); ?></th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="mobile-cards" id="leaderboardCards"></div>

  <?php if (isFeatureEnabled('leaderboard_chart')): ?>
  <section class="leaderboard-chart-panel">
    <div class="leaderboard-chart-header">
      <h2><?php echo htmlspecialchars(dcs_t('leaderboard.chart_title')); ?></h2>
      <div class="leaderboard-chart-controls">
        <label>
          <?php echo htmlspecialchars(dcs_t('leaderboard.chart_data')); ?>
          <select id="leaderboardChartMetric">
            <option value="kills"><?php echo htmlspecialchars(dcs_t('leaderboard.kills')); ?></option>
            <option value="deaths"><?php echo htmlspecialchars(dcs_t('leaderboard.deaths')); ?></option>
            <option value="kd_ratio"><?php echo htmlspecialchars(dcs_t('leaderboard.kd')); ?></option>
            <option value="kdr_pvp"><?php echo htmlspecialchars(dcs_t('leaderboard.pvp_kd')); ?></option>
            <option value="credits"><?php echo htmlspecialchars(dcs_t('leaderboard.credits')); ?></option>
            <option value="playtime_hours"><?php echo htmlspecialchars(dcs_t('leaderboard.playtime_hours')); ?></option>
            <option value="takeoffs"><?php echo htmlspecialchars(dcs_t('leaderboard.takeoffs')); ?></option>
            <option value="landings"><?php echo htmlspecialchars(dcs_t('leaderboard.landings')); ?></option>
            <option value="crashes"><?php echo htmlspecialchars(dcs_t('leaderboard.crashes')); ?></option>
            <option value="ejections"><?php echo htmlspecialchars(dcs_t('leaderboard.ejections')); ?></option>
          </select>
        </label>
      </div>
    </div>
    <div class="leaderboard-chart-frame">
      <canvas id="leaderboardChart"></canvas>
    </div>
  </section>
  <?php endif; ?>
</main>
