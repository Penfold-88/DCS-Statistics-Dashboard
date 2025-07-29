<?php include "header.php"; include "nav.php"; ?>

<main class="container">
  <h1>Leaderboard</h1>
  <div id="leaderboard-loading">Loading leaderboard...</div>

  <div id="top3-wrapper">
    <div class="top-3-container" id="top3-leaderboard"></div>
  </div>

  <div class="search-bar" style="text-align: center;">
    <input type="text" id="searchInput" placeholder="Search by name..." style="margin: 0 auto; width: 50%;">
  </div>

  <div class="table-responsive">
    <table id="leaderboardTable">
      <thead>
        <tr>
          <th>Rank</th>
          <th>Name</th>
          <th>Kills</th>
          <th>Sorties</th>
          <th>Takeoffs</th>
          <th>Landings</th>
          <th>Crashes</th>
          <th>Ejections</th>
          <th>Most Used Aircraft</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div id="pagination" class="pagination-container" style="text-align: center;"></div>
</main>

<script>
let leaderboardData = [];
const rowsPerPage = 20;
let currentPage = 1;

function renderTable() {
  const tbody = document.querySelector("#leaderboardTable tbody");
  const searchQuery = document.getElementById("searchInput").value.toLowerCase();
  const start = (currentPage - 1) * rowsPerPage;
  const filteredData = leaderboardData.filter(p => p.name.toLowerCase().includes(searchQuery));
  const paginatedData = filteredData.slice(start, start + rowsPerPage);

  tbody.innerHTML = "";
  paginatedData.forEach(player => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${escapeHtml(String(player.rank))}</td>
      <td>${escapeHtml(player.name || '')}</td>
      <td>${escapeHtml(String(player.kills || 0))}</td>
      <td>${escapeHtml(String(player.sorties || 0))}</td>
      <td>${escapeHtml(String(player.takeoffs || 0))}</td>
      <td>${escapeHtml(String(player.landings || 0))}</td>
      <td>${escapeHtml(String(player.crashes || 0))}</td>
      <td>${escapeHtml(String(player.ejections || 0))}</td>
      <td>${escapeHtml(player.most_used_aircraft || '')}</td>
    `;
    tbody.appendChild(row);
  });
}

function renderPagination() {
  const container = document.getElementById("pagination");
  const totalPages = Math.ceil(
    leaderboardData.filter(p => p.name.toLowerCase().includes(document.getElementById("searchInput").value.toLowerCase())).length / rowsPerPage
  );
  let html = '';
  html += `<button onclick="goToPage(${Math.max(currentPage - 1, 1)})">Prev</button>`;
  html += ` Page ${currentPage} of ${totalPages} `;
  html += `<button onclick="goToPage(${Math.min(currentPage + 1, totalPages)})">Next</button>`;
  container.innerHTML = html;
}

function goToPage(page) {
  currentPage = page;
  renderTable();
  renderPagination();
}

document.getElementById("searchInput").addEventListener("input", () => {
  currentPage = 1;
  renderTable();
  renderPagination();
});

async function loadLeaderboardFromMissionstats() {
  try {
    const response = await fetch('get_leaderboard.php');
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    leaderboardData = data;
    document.getElementById("leaderboard-loading").style.display = "none";
    
    // Populate top 3 leaderboard
    const top3Container = document.getElementById("top3-leaderboard");
    const trophies = ['🥇', '🥈', '🥉'];
    data.slice(0, 3).forEach((player, i) => {
        const box = document.createElement("div");
        box.className = "trophy-box";
        box.innerHTML = `<span class="trophy">${trophies[i]}</span><strong>${escapeHtml(player.name || '')}</strong><br>${escapeHtml(String(player.kills || 0))} kills`;
        top3Container.appendChild(box);
    });
    
    renderTable();
    renderPagination();
  } catch (error) {
    document.getElementById("leaderboard-loading").innerText = "Failed to load leaderboard data. Please try again later.";
    console.error("Error loading leaderboard:", error);
  }
}

// Load the leaderboard
loadLeaderboardFromMissionstats();
</script>

<?php include "footer.php"; ?>
