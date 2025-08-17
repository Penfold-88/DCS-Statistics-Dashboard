<?php 
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "header.php"; 
require_once __DIR__ . '/site_features.php';
require_once __DIR__ . '/table-responsive.php';
include "nav.php";

if (!isFeatureEnabled('nav_leaderboard')) {
    echo '<main><div class="alert" style="text-align: center; padding: 50px;">'
       . '<h2>Leaderboard Disabled</h2>'
       . '<p>The leaderboard is currently disabled.</p>'
       . '</div></main>';
    include 'footer.php';
    exit;
}
?>

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
</style>

<?php tableResponsiveStyles(); ?>

<main class="container">
  <div class="dashboard-header">
    <h1>Leaderboard</h1>
    <p class="dashboard-subtitle">Top 10 pilots ranked by kills</p>
  </div>
  <div id="leaderboard-loading">Loading leaderboard...</div>

  <div id="top3-wrapper">
    <div class="top-3-container" id="top3-leaderboard"></div>
  </div>

  <div class="table-responsive">
    <table id="leaderboardTable">
      <thead>
        <tr>
          <th>Rank</th>
          <th>Name</th>
          <?php if (isFeatureEnabled('leaderboard_kills')): ?>
          <th>Kills</th>
          <?php endif; ?>
          <?php if (isFeatureEnabled('leaderboard_sorties')): ?>
          <th>Sorties</th>
          <?php endif; ?>
          <th>Takeoffs</th>
          <th>Landings</th>
          <th>Crashes</th>
          <th>Ejections</th>
          <?php if (isFeatureEnabled('leaderboard_aircraft')): ?>
          <th>Most Used Aircraft</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
  
  <!-- Mobile Cards Container -->
  <div class="mobile-cards" id="leaderboardCards"></div>
</main>

<script>
let leaderboardData = [];

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
    
    <?php if (isFeatureEnabled('leaderboard_sorties')): ?>
    cells += `<td>${escapeHtml(String(player.sorties || 0))}</td>`;
    <?php endif; ?>
    
    cells += `
      <td>${escapeHtml(String(player.takeoffs || 0))}</td>
      <td>${escapeHtml(String(player.landings || 0))}</td>
      <td>${escapeHtml(String(player.crashes || 0))}</td>
      <td>${escapeHtml(String(player.ejections || 0))}</td>`;
    
    <?php if (isFeatureEnabled('leaderboard_aircraft')): ?>
    cells += `<td>${escapeHtml(player.most_used_aircraft || '')}</td>`;
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
    cardHtml += `<div class="leaderboard-card-stat">Kills: <span>${escapeHtml(String(player.kills || 0))}</span></div>`;
    <?php endif; ?>
    
    <?php if (isFeatureEnabled('leaderboard_deaths')): ?>
    cardHtml += `<div class="leaderboard-card-stat">Deaths: <span>${escapeHtml(String(player.deaths || 0))}</span></div>`;
    <?php endif; ?>
    
    <?php if (isFeatureEnabled('leaderboard_kd_ratio')): ?>
    cardHtml += `<div class="leaderboard-card-stat">K/D: <span>${escapeHtml(String(player.kd_ratio || 0))}</span></div>`;
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
    // Use the client-side API
    const result = await window.dcsAPI.getLeaderboard();
    
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
        box.innerHTML = `<span class="trophy">${trophies[i]}</span><strong>${escapeHtml(player.nick || '')}</strong><br>${escapeHtml(String(player.kills || 0))} kills`;
        top3Container.appendChild(box);
    });
    
    renderTable();
  } catch (error) {
    document.getElementById("leaderboard-loading").innerText = "Failed to load leaderboard data. Please try again later.";
    console.error("Error loading leaderboard:", error);
  }
}

// Load the leaderboard
loadLeaderboardFromMissionstats();
</script>

<?php include "footer.php"; ?>
