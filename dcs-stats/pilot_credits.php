<?php include 'header.php'; ?>
<?php include 'nav.php'; ?>

<main>
    <h2>Pilot Credits</h2>

    <div id="top3-wrapper">
        <div class="top-3-container" id="top3"></div>
    </div>

    <div class="search-container">
        <input type="text" id="searchBox" placeholder="Search player name...">
    </div>

    <table class="credits-table" id="creditsTable">
        <thead>
            <tr><th>Player</th><th>Credits</th></tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="pagination">
        <button id="prevBtn">Previous</button>
        <span id="pageInfo"></span>
        <button id="nextBtn">Next</button>
    </div>
</main>

<script>
let currentPage = 1;
const rowsPerPage = 20;
let allData = [];
let filteredData = [];

function renderPage(data, page) {
    const tableBody = document.querySelector("#creditsTable tbody");
    tableBody.innerHTML = "";
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = data.slice(start, end);

    pageData.forEach(player => {
        const row = document.createElement("tr");
        row.innerHTML = `<td>${escapeHtml(player.name || '')}</td><td>${escapeHtml(String(player.credits || 0))}</td>`;
        tableBody.appendChild(row);
    });

    document.getElementById("pageInfo").textContent = `Page ${page} of ${Math.ceil(data.length / rowsPerPage)}`;
    document.getElementById("prevBtn").disabled = (page === 1);
    document.getElementById("nextBtn").disabled = (end >= data.length);
}

function updateSearch() {
    const query = document.getElementById("searchBox").value.toLowerCase();
    filteredData = allData.filter(p => p.name.toLowerCase().includes(query));
    currentPage = 1;
    renderPage(filteredData, currentPage);
}

document.getElementById("prevBtn").addEventListener("click", () => {
    if (currentPage > 1) {
        currentPage--;
        renderPage(filteredData, currentPage);
    }
});

document.getElementById("nextBtn").addEventListener("click", () => {
    if ((currentPage * rowsPerPage) < filteredData.length) {
        currentPage++;
        renderPage(filteredData, currentPage);
    }
});

document.getElementById("searchBox").addEventListener("input", updateSearch);

async function loadCreditsData() {
  try {
    const response = await fetch('get_credits.php');
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    allData = data;
    filteredData = data;

    const top3Container = document.getElementById("top3");
    const trophies = ['🥇', '🥈', '🥉'];
    data.slice(0, 3).forEach((player, i) => {
        const box = document.createElement("div");
        box.className = "trophy-box";
        box.innerHTML = `<span class="trophy">${trophies[i]}</span><strong>${escapeHtml(player.name || '')}</strong><br>${escapeHtml(String(player.credits || 0))} credits`;
        top3Container.appendChild(box);
    });

    renderPage(filteredData, currentPage);
  } catch (error) {
    console.error("Error loading credits data:", error);
    const top3Container = document.getElementById("top3");
    top3Container.innerHTML = "<p>Failed to load credits data. Please try again later.</p>";
  }
}

// Load credits data
loadCreditsData();
</script>

<?php include 'footer.php'; ?>
