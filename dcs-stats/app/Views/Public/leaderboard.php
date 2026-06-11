<?php
include DCS_APP_PATH . '/Views/Layout/header.php';
include DCS_APP_PATH . '/Views/Layout/nav.php';
?>

<?php $chartTheme = loadChartTheme(); ?>

<style>
  /* Professional Leaderboard Styling */
  #leaderboardTable {
    background: rgba(0, 0, 0, 0.6);
    border: 1px solid rgba(76, 175, 80, 0.3);
  }
  
  #leaderboardTable tbody tr {
    background: rgba(0, 0, 0, 0.3);
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
  }
  
  #leaderboardTable tbody tr:hover {
    background: rgba(76, 175, 80, 0.05);
    border-left-color: #4CAF50;
    transform: translateX(3px);
  }
  
  .player-name a {
    display: inline-block;
    color: #e0e0e0;
    text-decoration: none;
    transition: color 0.2s ease;
    font-weight: 500;
  }
  
  .player-name a:hover {
    color: #4CAF50;
  }
  
  /* Rank styling */
  #leaderboardTable tbody tr td:first-child {
    font-weight: 600;
    color: #4CAF50;
  }
  
  /* Top 3 trophy boxes professional styling */
  .trophy-box {
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.1) 0%, rgba(0, 0, 0, 0.3) 100%);
    border: 1px solid rgba(76, 175, 80, 0.3);
    padding: 20px;
    text-align: center;
    border-radius: 4px;
    transition: all 0.3s ease;
  }
  
  .trophy-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
    border-color: #4CAF50;
  }
  
  .trophy {
    font-size: 2rem;
    display: block;
    margin-bottom: 10px;
  }
  
  .trophy-box strong {
    color: #4CAF50;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .leaderboard-chart-panel {
    background: linear-gradient(135deg, #2c2c2c 0%, #1e1e1e 100%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    padding: 24px;
    margin: 30px 0;
    overflow: hidden;
  }

  .leaderboard-chart-header {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  .leaderboard-chart-header h2 {
    color: #4CAF50;
    margin: 0;
    text-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
  }

  .leaderboard-chart-controls {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  .leaderboard-chart-controls label {
    color: #ccc;
    display: grid;
    gap: 6px;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .leaderboard-chart-controls select {
    background: rgba(0, 0, 0, 0.45);
    border: 1px solid rgba(76, 175, 80, 0.35);
    border-radius: 8px;
    color: #fff;
    padding: 9px 12px;
    min-width: 150px;
  }

  .leaderboard-chart-frame {
    position: relative;
    min-height: 360px;
    overflow: hidden;
    width: 100%;
  }

  .leaderboard-chart-frame canvas {
    display: block;
    max-width: 100%;
  }

  .leaderboard-page {
    width: min(98vw, 1680px);
    max-width: 1680px;
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .leaderboard-page .table-responsive {
    border: 1px solid rgba(76, 175, 80, 0.25);
  }

  #leaderboardTable {
    table-layout: auto;
    min-width: 1180px;
  }

  #leaderboardTable th,
  #leaderboardTable td {
    padding: 12px 14px;
    white-space: nowrap;
  }

  #leaderboardTable th:nth-child(2),
  #leaderboardTable td:nth-child(2) {
    min-width: 190px;
  }

  #leaderboardTable th:last-child,
  #leaderboardTable td:last-child {
    min-width: 160px;
  }

  @media (min-width: 1300px) {
    #leaderboardTable {
      min-width: 0;
    }
  }

  @media (max-width: 640px) {
    .leaderboard-chart-panel {
      padding: 16px;
    }

    .leaderboard-chart-header {
      align-items: stretch;
      flex-direction: column;
    }

    .leaderboard-chart-controls select {
      width: 100%;
    }
  }
</style>

<?php tableResponsiveStyles(); ?>

<?php include DCS_APP_PATH . '/Views/Public/leaderboard/content.php'; ?>

<script>
let leaderboardData = [];
const leaderboardChartTheme = <?php echo json_encode($chartTheme); ?>;
const needsLeaderboardPlayerDetails = <?php echo json_encode(
  isFeatureEnabled('leaderboard_chart') ||
  isFeatureEnabled('leaderboard_sorties') ||
  isFeatureEnabled('leaderboard_takeoffs') ||
  isFeatureEnabled('leaderboard_landings') ||
  isFeatureEnabled('leaderboard_crashes') ||
  isFeatureEnabled('leaderboard_ejections') ||
  isFeatureEnabled('leaderboard_aircraft')
); ?>;
const i18n = <?php echo json_encode([
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
  'loadError' => dcs_t('leaderboard.load_error')
], JSON_UNESCAPED_UNICODE); ?>;
const leaderboardChartMetrics = {
  kills: { label: i18n.kills, value: player => Number(player.kills || 0) },
  deaths: { label: i18n.deaths, value: player => Number(player.deaths || 0) },
  kd_ratio: { label: i18n.kd, value: player => Number(player.kd_ratio || player.kdr || 0) },
  kdr_pvp: { label: i18n.pvpKd, value: player => Number(player.kdr_pvp || 0) },
  credits: { label: i18n.credits, value: player => Number(player.credits || 0) },
  playtime_hours: { label: i18n.playtimeHours, value: player => Math.round(Number(player.playtime || 0) / 3600) },
  takeoffs: { label: i18n.takeoffs, value: player => Number(player.takeoffs || 0) },
  landings: { label: i18n.landings, value: player => Number(player.landings || 0) },
  crashes: { label: i18n.crashes, value: player => Number(player.crashes || 0) },
  ejections: { label: i18n.ejections, value: player => Number(player.ejections || 0) }
};

function renderTable() {
  const tbody = document.querySelector("#leaderboardTable tbody");
  const mobileCards = document.querySelector("#leaderboardCards");
  
  // Only show top 10 players
  const top10Data = leaderboardData.slice(0, 10);

  tbody.innerHTML = "";
  mobileCards.innerHTML = "";
  
  top10Data.forEach(player => {
    const row = document.createElement("tr");
    row.style.cursor = "pointer";
    row.style.transition = "background-color 0.2s ease";
    
    let cells = `
      <td>${escapeHtml(String(player.rank))}</td>
      <td class="player-name">
        <a href="pilot_statistics.php?search=${encodeURIComponent(player.nick || '')}" style="color: inherit; text-decoration: none;">
          ${escapeHtml(player.nick || '')}
        </a>
      </td>`;
    
    <?php if (isFeatureEnabled('leaderboard_kills')): ?>
    cells += `<td>${escapeHtml(String(player.kills || 0))}</td>`;
    <?php endif; ?>

    <?php if (isFeatureEnabled('leaderboard_deaths')): ?>
    cells += `<td>${escapeHtml(String(player.deaths || 0))}</td>`;
    <?php endif; ?>

    <?php if (isFeatureEnabled('leaderboard_kd_ratio')): ?>
    cells += `<td>${escapeHtml(String(player.kd_ratio || player.kdr || 0))}</td>`;
    <?php endif; ?>

    <?php if (isFeatureEnabled('leaderboard_pvp_kd_ratio')): ?>
    cells += `<td>${escapeHtml(String(player.kdr_pvp || 0))}</td>`;
    <?php endif; ?>

    <?php if (isFeatureEnabled('leaderboard_credits')): ?>
    cells += `<td>${escapeHtml(String(player.credits || 0))}</td>`;
    <?php endif; ?>

    <?php if (isFeatureEnabled('leaderboard_playtime')): ?>
    cells += `<td>${escapeHtml(formatPlaytime(player.playtime || 0))}</td>`;
    <?php endif; ?>
    
    <?php if (isFeatureEnabled('leaderboard_sorties')): ?>
    cells += `<td class="col-sorties">${escapeHtml(formatOptionalNumber(player.sorties))}</td>`;
    <?php endif; ?>
    
    <?php if (isFeatureEnabled('leaderboard_takeoffs')): ?>
    cells += `<td>${escapeHtml(formatOptionalNumber(player.takeoffs))}</td>`;
    <?php endif; ?>

    <?php if (isFeatureEnabled('leaderboard_landings')): ?>
    cells += `<td>${escapeHtml(formatOptionalNumber(player.landings))}</td>`;
    <?php endif; ?>

    <?php if (isFeatureEnabled('leaderboard_crashes')): ?>
    cells += `<td>${escapeHtml(formatOptionalNumber(player.crashes))}</td>`;
    <?php endif; ?>

    <?php if (isFeatureEnabled('leaderboard_ejections')): ?>
    cells += `<td>${escapeHtml(formatOptionalNumber(player.ejections))}</td>`;
    <?php endif; ?>
    
    <?php if (isFeatureEnabled('leaderboard_aircraft')): ?>
    cells += `<td>${escapeHtml(player.most_used_aircraft || '-')}</td>`;
    <?php endif; ?>
    
    row.innerHTML = cells;
    
    // Add hover effect
    row.addEventListener('mouseenter', function() {
      this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
    });
    row.addEventListener('mouseleave', function() {
      this.style.backgroundColor = '';
    });
    
    // Add click handler for the entire row
    row.addEventListener('click', function() {
      window.location.href = `pilot_statistics.php?search=${encodeURIComponent(player.nick || '')}`;
    });
    
    tbody.appendChild(row);
    
    // Create mobile card
    const card = document.createElement('div');
    card.className = 'mobile-card leaderboard-card';
    card.style.cursor = 'pointer';
    
    let cardHtml = `
      <div class="leaderboard-card-left">
        <div class="leaderboard-card-rank">#${escapeHtml(String(player.rank))}</div>
        <div class="leaderboard-card-name">${escapeHtml(player.nick || '')}</div>
        <div class="leaderboard-card-stats">
    `;
    
    <?php if (isFeatureEnabled('leaderboard_kills')): ?>
    cardHtml += `<div class="leaderboard-card-stat">${escapeHtml(i18n.kills)}: <span>${escapeHtml(String(player.kills || 0))}</span></div>`;
    <?php endif; ?>
    
    <?php if (isFeatureEnabled('leaderboard_deaths')): ?>
    cardHtml += `<div class="leaderboard-card-stat">${escapeHtml(i18n.deaths)}: <span>${escapeHtml(String(player.deaths || 0))}</span></div>`;
    <?php endif; ?>
    
    <?php if (isFeatureEnabled('leaderboard_kd_ratio')): ?>
    cardHtml += `<div class="leaderboard-card-stat">${escapeHtml(i18n.kd)}: <span>${escapeHtml(String(player.kd_ratio || 0))}</span></div>`;
    <?php endif; ?>
    
    cardHtml += `
        </div>
      </div>
    `;
    
    card.innerHTML = cardHtml;
    
    // Add click handler for card
    card.addEventListener('click', function() {
      window.location.href = `pilot_statistics.php?search=${encodeURIComponent(player.nick || '')}`;
    });
    
    mobileCards.appendChild(card);
  });

}

async function loadLeaderboardFromMissionstats() {
  try {
    document.getElementById("leaderboard-loading").style.display = "block";
    document.getElementById("leaderboard-loading").innerText = <?php echo json_encode(dcs_t('leaderboard.loading')); ?>;
    document.getElementById("top3-leaderboard").innerHTML = "";

    // Use the client-side API
    const result = await window.dcsAPI.getLeaderboard({ loadPlayerDetails: needsLeaderboardPlayerDetails });
    
    // Handle both direct array response and wrapped response
    let data = result;
    if (result.data && Array.isArray(result.data)) {
        data = result.data;
        // Show data source if available
        if (result.source) {
        }
    }
    // Only keep top 10 players
    leaderboardData = data.slice(0, 10);
    document.getElementById("leaderboard-loading").style.display = "none";
    
    // Populate top 3 leaderboard
    const top3Container = document.getElementById("top3-leaderboard");
    const trophies = ['🥇', '🥈', '🥉'];
    leaderboardData.slice(0, 3).forEach((player, i) => {
        const box = document.createElement("div");
        box.className = "trophy-box";
        box.innerHTML = `<span class="trophy">${trophies[i]}</span><strong>${escapeHtml(player.nick || '')}</strong><br>${escapeHtml(String(player.kills || 0))} ${escapeHtml(i18n.kills)}`;
        top3Container.appendChild(box);
    });
    
    renderTable();
    renderLeaderboardChart();
  } catch (error) {
    document.getElementById("leaderboard-loading").innerText = i18n.loadError;
    console.error("Error loading leaderboard:", error);
  }
}

function renderLeaderboardChart() {
  const canvas = document.getElementById('leaderboardChart');
  const metricSelect = document.getElementById('leaderboardChartMetric');
  if (!canvas || !metricSelect || !leaderboardData.length) return;

  const metric = leaderboardChartMetrics[metricSelect.value] || leaderboardChartMetrics.kills;
  const labels = leaderboardData.slice(0, 10).map(player => player.nick || i18n.unknown);
  const values = leaderboardData.slice(0, 10).map(metric.value);
  drawLeaderboardCanvas(canvas, labels, values, metric.label);
}

function drawLeaderboardCanvas(canvas, labels, values, metricLabel) {
  const frame = canvas.parentElement;
  const dpr = window.devicePixelRatio || 1;
  const width = Math.max(1, Math.floor(frame.clientWidth));
  const height = width < 520 ? 320 : 360;
  canvas.width = width * dpr;
  canvas.height = height * dpr;
  canvas.style.width = `${width}px`;
  canvas.style.height = `${height}px`;

  const ctx = canvas.getContext('2d');
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.clearRect(0, 0, width, height);

  const padding = width < 520
    ? { top: 32, right: 12, bottom: 74, left: 42 }
    : { top: 32, right: 28, bottom: 84, left: 58 };
  const chartWidth = width - padding.left - padding.right;
  const chartHeight = height - padding.top - padding.bottom;
  const maxValue = Math.max(...values, 1);
  const stepCount = 4;

  ctx.font = width < 520 ? '600 12px Arial, sans-serif' : '600 14px Arial, sans-serif';
  ctx.fillStyle = leaderboardChartTheme.chart_text_color;
  ctx.fillText(metricLabel, padding.left, 20);

  ctx.strokeStyle = hexToRgba(leaderboardChartTheme.chart_grid_color, 0.45);
  ctx.lineWidth = 1;
  ctx.font = width < 520 ? '10px Arial, sans-serif' : '12px Arial, sans-serif';
  for (let i = 0; i <= stepCount; i++) {
    const ratio = i / stepCount;
    const y = padding.top + chartHeight - (chartHeight * ratio);
    const value = Math.round(maxValue * ratio);
    ctx.beginPath();
    ctx.moveTo(padding.left, y);
    ctx.lineTo(width - padding.right, y);
    ctx.stroke();
    ctx.fillStyle = leaderboardChartTheme.chart_text_color;
    ctx.fillText(value.toLocaleString(), 8, y + 4);
  }

  const slotWidth = chartWidth / labels.length;
  const points = values.map((value, index) => {
    const x = padding.left + slotWidth * index + slotWidth / 2;
    const y = padding.top + chartHeight - (Number(value || 0) / maxValue) * chartHeight;
    return { x, y, value };
  });

  const barWidth = Math.max(8, Math.min(width < 520 ? 32 : 56, slotWidth * 0.58));
  points.forEach(point => {
    const barHeight = padding.top + chartHeight - point.y;
    const x = point.x - barWidth / 2;
    ctx.fillStyle = hexToRgba(leaderboardChartTheme.chart_primary_color, 0.78);
    roundRect(ctx, x, point.y, barWidth, barHeight, 8);
    ctx.fill();
  });

  ctx.fillStyle = leaderboardChartTheme.chart_text_color;
  ctx.font = width < 520 ? '10px Arial, sans-serif' : '11px Arial, sans-serif';
  labels.forEach((label, index) => {
    const x = padding.left + slotWidth * index + slotWidth / 2;
    ctx.save();
    ctx.translate(x, height - (width < 520 ? 46 : 58));
    ctx.rotate(width < 520 ? -0.7 : -0.45);
    ctx.textAlign = 'right';
    const maxLabelLength = width < 520 ? 12 : 20;
    ctx.fillText(label.length > maxLabelLength ? `${label.slice(0, maxLabelLength - 2)}...` : label, 0, 0);
    ctx.restore();
  });
}

function roundRect(ctx, x, y, width, height, radius) {
  const safeRadius = Math.min(radius, width / 2, Math.max(0, height / 2));
  ctx.beginPath();
  ctx.moveTo(x + safeRadius, y);
  ctx.lineTo(x + width - safeRadius, y);
  ctx.quadraticCurveTo(x + width, y, x + width, y + safeRadius);
  ctx.lineTo(x + width, y + height);
  ctx.lineTo(x, y + height);
  ctx.lineTo(x, y + safeRadius);
  ctx.quadraticCurveTo(x, y, x + safeRadius, y);
  ctx.closePath();
}

function hexToRgba(hex, alpha) {
  const clean = String(hex || '#4CAF50').replace('#', '');
  const value = parseInt(clean, 16);
  const red = (value >> 16) & 255;
  const green = (value >> 8) & 255;
  const blue = value & 255;
  return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('leaderboardChartMetric')?.addEventListener('change', renderLeaderboardChart);
});

let leaderboardChartResizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(leaderboardChartResizeTimer);
  leaderboardChartResizeTimer = setTimeout(renderLeaderboardChart, 120);
});

function formatPlaytime(seconds) {
  const hours = Math.floor(Number(seconds || 0) / 3600);
  return `${hours.toLocaleString()}h`;
}

function formatOptionalNumber(value) {
  if (value === null || value === undefined || value === '') {
    return '-';
  }
  return Number(value).toLocaleString();
}

// Load the leaderboard
loadLeaderboardFromMissionstats();
window.addEventListener('dcs-server-scope-change', loadLeaderboardFromMissionstats);
</script>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
