<?php
include 'header.php';
include 'nav.php';
?>

<main>
    <h1>Squadrons</h1>

    <div style="display: flex; justify-content: center; margin-bottom: 20px;">
        <input type="text" id="searchInput" placeholder="Search squadrons, members, or credits..." style="width: 60%; padding: 10px; font-size: 16px;">
    </div>

    <div class="table-responsive">
        <table id="squadronsTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 10%;">Logo</th>
                    <th style="width: 40%;">Name</th>
                    <th style="width: 50%;">Description</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <h2>Squadron Members</h2>
    <div class="table-responsive">
        <table id="membersTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 10%;">Logo</th>
                    <th style="width: 30%;">Squadron Name</th>
                    <th style="width: 60%;">Member</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <h2>Squadron Leaderboard</h2>
    <div class="table-responsive">
        <table id="leaderboardTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 10%;">Logo</th>
                    <th style="width: 60%;">Squadron Name</th>
                    <th style="width: 30%;">Credits</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div id="error-message" style="color: red; text-align: center;"></div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('searchInput');

    Promise.all([
        fetch('get_squadron.php?file=squadrons').then(res => res.text()),
        fetch('get_squadron.php?file=squadron_members').then(res => res.text()),
        fetch('get_squadron.php?file=players').then(res => res.text()),
        fetch('get_squadron.php?file=squadron_credits').then(res => res.text())
    ])
    .then(([squadronText, memberText, playerText, creditText]) => {
        const squadrons = squadronText.trim().split('\n').map(line => JSON.parse(line));
        const members = memberText.trim().split('\n').map(line => JSON.parse(line));
        const players = playerText.trim().split('\n').map(line => JSON.parse(line));
        const credits = creditText.trim().split('\n').map(line => JSON.parse(line));

        const squadronsById = Object.fromEntries(squadrons.map(sq => [sq.id, sq]));
        const playersByUcid = Object.fromEntries(players.map(p => [p.ucid, p]));

        const squadronBody = document.querySelector('#squadronsTable tbody');
        const membersBody = document.querySelector('#membersTable tbody');
        const leaderboardBody = document.querySelector('#leaderboardTable tbody');

        const medals = ['🥇', '🥈', '🥉'];

        function renderTables(filter = '') {
            squadronBody.innerHTML = '';
            membersBody.innerHTML = '';
            leaderboardBody.innerHTML = '';

            // === Squadrons Table ===
            squadrons.forEach(sq => {
                if (!sq.name.toLowerCase().includes(filter) && !sq.description.toLowerCase().includes(filter)) return;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><img src="${escapeHtml(sq.image_url || '')}" alt="${escapeHtml(sq.name || '')}" style="width: 80px;"></td>
                    <td>${escapeHtml(sq.name || '')}</td>
                    <td>${escapeHtml(sq.description || '')}</td>
                `;
                squadronBody.appendChild(row);
            });

            // === Members Table ===
            squadrons.forEach((sq, index) => {
                const relevantMembers = members.filter(m => m.squadron_id === sq.id);
                const groupId = "group-" + index;
                const lowerName = sq.name.toLowerCase();
                const matchFound = lowerName.includes(filter) || relevantMembers.some(m => playersByUcid[m.player_ucid]?.name.toLowerCase().includes(filter));
                if (!matchFound) return;

                const headerRow = document.createElement('tr');
                headerRow.classList.add('toggle-header');
                headerRow.style.cursor = "pointer";
                headerRow.innerHTML = `
                    <td><img src="${escapeHtml(sq.image_url || '')}" alt="${escapeHtml(sq.name || '')}" style="width: 60px;"></td>
                    <td>${escapeHtml(sq.name || '')}</td>
                    <td><em>Click to show/hide members</em></td>
                `;
                membersBody.appendChild(headerRow);

                relevantMembers.forEach(member => {
                    const player = playersByUcid[member.player_ucid];
                    if (!player || !player.name.toLowerCase().includes(filter) && !lowerName.includes(filter)) return;

                    const row = document.createElement('tr');
                    row.classList.add(groupId);
                    row.style.display = "none";
                    row.innerHTML = `
                        <td></td>
                        <td></td>
                        <td>${escapeHtml(player.name || '')}</td>
                    `;
                    membersBody.appendChild(row);
                });

                headerRow.addEventListener('click', () => {
                    const groupRows = document.querySelectorAll('.' + groupId);
                    groupRows.forEach(r => {
                        r.style.display = (r.style.display === 'none') ? 'table-row' : 'none';
                    });
                });
            });

            // === Leaderboard Table ===
            credits
                .sort((a, b) => b.points - a.points)
                .forEach((entry, index) => {
                    const squadron = squadronsById[entry.squadron_id];
                    if (!squadron) return;
                    if (!squadron.name.toLowerCase().includes(filter)) return;

                    const medal = medals[index] || '';
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><img src="${escapeHtml(squadron.image_url || '')}" alt="${escapeHtml(squadron.name || '')}" style="width: 60px;"></td>
                        <td>${medal} ${escapeHtml(squadron.name || '')}</td>
                        <td>${escapeHtml(String(entry.points || 0))}</td>
                    `;
                    leaderboardBody.appendChild(row);
                });
        }

        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase().trim();
            renderTables(filter);
        });

        renderTables(); // Initial load
    })
    .catch(err => {
        document.getElementById('error-message').textContent = "Error loading data: " + err.message;
        console.error(err);
    });
});
</script>

<?php
include 'footer.php';
?>
