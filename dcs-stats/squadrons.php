<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php';
require_once __DIR__ . '/site_features.php';
require_once __DIR__ . '/table-responsive.php';
include 'nav.php';

if (!isFeatureEnabled('squadrons_enabled')):
?>
<main>
    <div class="alert" style="text-align: center; padding: 50px;">
        <h2>Squadron System Disabled</h2>
        <p>The squadron system is currently disabled for this server.</p>
    </div>
</main>
<?php include 'footer.php'; exit; ?>
<?php endif; ?>

<style>
    /* Squadron Tables Professional Styling */
    .table-responsive {
        margin-bottom: 40px;
    }
    
    #squadronsTable, #membersTable, #leaderboardTable {
        background: rgba(0, 0, 0, 0.6);
        border: 1px solid rgba(76, 175, 80, 0.3);
    }
    
    /* Squadron Headers */
    h2 {
        color: #4CAF50;
        font-size: 1.5rem;
        font-weight: 600;
        margin: 30px 0 20px 0;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        padding-left: 20px;
    }
    
    h2:before {
        content: "▪";
        position: absolute;
        left: 0;
        color: #4CAF50;
    }
    
    /* Squadron toggle headers */
    .toggle-header {
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.1) 0%, rgba(0, 0, 0, 0.3) 100%);
        border-bottom: 2px solid rgba(76, 175, 80, 0.3);
        transition: all 0.3s ease;
    }
    
    .toggle-header:hover {
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.2) 0%, rgba(0, 0, 0, 0.4) 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
    }
    
    .toggle-header td:last-child {
        position: relative;
        padding-right: 40px;
    }
    
    .toggle-header td:last-child::after {
        content: "▼";
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #4CAF50;
        font-size: 12px;
        transition: transform 0.3s ease;
    }
    
    .toggle-header.expanded td:last-child::after {
        transform: translateY(-50%) rotate(180deg);
    }
    
    /* Click to expand text styling */
    .toggle-header em {
        font-style: normal;
        color: #999;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .toggle-header:hover em {
        color: #4CAF50;
    }
    
    /* Squadron member rows */
    #membersTable tbody tr:not(.toggle-header) {
        background: rgba(0, 0, 0, 0.3);
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }
    
    #membersTable tbody tr:not(.toggle-header):hover {
        background: rgba(76, 175, 80, 0.05);
        border-left-color: #4CAF50;
        transform: translateX(3px);
    }
    
    /* Member name styling */
    .member-name a {
        display: inline-block;
        color: #e0e0e0;
        text-decoration: none;
        transition: color 0.2s ease;
        font-weight: 500;
    }
    
    .member-name a:hover {
        color: #4CAF50;
    }
    
    .member-name small {
        color: #777;
        font-size: 0.85rem;
        margin-left: 10px;
    }
    
    /* Squadron images */
    #squadronsTable img, #membersTable img, #leaderboardTable img {
        border: 1px solid rgba(76, 175, 80, 0.3);
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    #squadronsTable tr:hover img, 
    #membersTable tr:hover img, 
    #leaderboardTable tr:hover img {
        border-color: #4CAF50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
    }
    
    /* Leaderboard specific styling */
    #leaderboardTable tbody tr {
        background: rgba(0, 0, 0, 0.3);
        transition: all 0.2s ease;
    }
    
    #leaderboardTable tbody tr:hover {
        background: rgba(76, 175, 80, 0.05);
        transform: translateY(-1px);
    }
    
    /* Trophy medals with military rank feel */
    .medals {
        display: inline-block;
        width: 30px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        border-radius: 50%;
        font-size: 1.2rem;
        margin-right: 10px;
    }
    
    /* Squadron description */
    #squadronsTable td:last-child {
        color: #999;
        font-size: 0.95rem;
        line-height: 1.4;
    }
    
    /* Member count badge */
    .member-count {
        display: inline-block;
        background: rgba(76, 175, 80, 0.2);
        color: #4CAF50;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
        margin-left: 10px;
    }
    
    /* Mobile Responsive Styles */
    @media screen and (max-width: 768px) {
        /* Remove fixed widths on mobile */
        #squadronsTable th,
        #squadronsTable td,
        #membersTable th,
        #membersTable td,
        #leaderboardTable th,
        #leaderboardTable td {
            width: auto !important;
        }
        
        /* Stack squadron info vertically on mobile */
        #squadronsTable td:first-child img {
            width: 50px !important;
            height: 50px !important;
            object-fit: cover;
        }
        
        #membersTable td:first-child img,
        #leaderboardTable td:first-child img {
            width: 40px !important;
            height: 40px !important;
            object-fit: cover;
        }
        
        /* Adjust font sizes */
        h2 {
            font-size: 1.2rem;
            margin: 20px 0 15px 0;
        }
        
        .dashboard-header h1 {
            font-size: 1.8rem;
        }
        
        .dashboard-subtitle {
            font-size: 0.9rem;
        }
        
        /* Make search container mobile-friendly */
        .search-container {
            padding: 0 10px;
            margin-bottom: 20px;
        }
        
        #searchInput {
            width: 100%;
            padding: 12px;
            font-size: 16px; /* Prevents zoom on iOS */
        }
        
        /* Adjust table text */
        table {
            font-size: 0.85rem;
        }
        
        /* Hide descriptions on very small screens */
        @media screen and (max-width: 480px) {
            #squadronsTable td:last-child {
                display: none;
            }
            
            #squadronsTable th:last-child {
                display: none;
            }
            
            .member-name small {
                display: block;
                margin-left: 0;
                margin-top: 5px;
            }
        }
        
        /* Improve toggle header on mobile */
        .toggle-header td {
            padding: 15px 10px;
        }
        
        .toggle-header em {
            font-size: 0.8rem;
        }
        
        /* Better member row styling on mobile */
        #membersTable tbody tr:not(.toggle-header) td {
            padding-left: 20px;
        }
    }
    
    /* Search container styling */
    .search-container {
        margin: 20px auto;
        max-width: 600px;
        text-align: center;
    }
    
    #searchInput {
        width: 100%;
        max-width: 100%;
        padding: 10px 15px;
        font-size: 1rem;
        background: rgba(0, 0, 0, 0.6);
        border: 1px solid rgba(76, 175, 80, 0.3);
        color: #fff;
        border-radius: 25px;
        transition: all 0.3s ease;
    }
    
    #searchInput:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
    }
    
    #searchInput::placeholder {
        color: #999;
    }
</style>

<?php tableResponsiveStyles(); ?>

<main>
    <div class="dashboard-header">
        <h1>Squadrons</h1>
        <p class="dashboard-subtitle">Squadron rankings and member information</p>
    </div>

    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search squadrons, members, or credits...">
    </div>

    <div class="table-wrapper">
        <table id="squadronsTable">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <?php if (isFeatureEnabled('squadron_management')): ?>
    <h2>Squadron Members</h2>
    <div class="table-wrapper">
        <table id="membersTable">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Squadron Name</th>
                    <th>Member</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (isFeatureEnabled('squadron_statistics') && isFeatureEnabled('credits_enabled')): ?>
    <h2>Squadron Leaderboard</h2>
    <div class="table-wrapper">
        <table id="leaderboardTable">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Squadron Name</th>
                    <th>Credits</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <?php endif; ?>

    <div id="error-message" style="color: red; text-align: center;"></div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('searchInput');

    // Helper to build URLs
    const basePath = window.DCS_CONFIG ? window.DCS_CONFIG.basePath : '';
    const buildUrl = (path) => basePath ? `${basePath}/${path}` : path;
    
    // Load squadron data from API
    async function loadSquadronData() {
        try {
            // First, get the list of squadrons
            const squadronsResponse = await fetch(buildUrl('get_squadrons.php'));
            if (!squadronsResponse.ok) {
                throw new Error('Failed to load squadrons');
            }
            const squadronsData = await squadronsResponse.json();
            const squadrons = squadronsData.data || [];
            
            // No need to load players separately - member names come from API
            
            // Load member and credit data for each squadron
            const squadronData = [];
            
            for (const squadron of squadrons) {
                const squadronInfo = {
                    ...squadron,
                    members: [],
                    totalCredits: 0
                };
                
                // Get squadron members
                try {
                    const membersResp = await fetch(buildUrl('get_squadron_members.php'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ name: squadron.name })
                    });
                    if (membersResp.ok) {
                        const membersData = await membersResp.json();
                        if (membersData.data) {
                            squadronInfo.members = membersData.data;
                            squadronInfo.member_count = membersData.data.length;
                        }
                    } else {
                        console.error(`Failed to fetch members for ${squadron.name}: ${membersResp.status}`);
                    }
                } catch (error) {
                    console.warn(`Failed to load members for squadron ${squadron.name}:`, error);
                }
                
                // Get squadron credits
                try {
                    const creditsResp = await fetch(buildUrl('get_squadron_credits.php'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ name: squadron.name })
                    });
                    if (creditsResp.ok) {
                        const creditsData = await creditsResp.json();
                        if (creditsData.data && Array.isArray(creditsData.data)) {
                            // Sum up credits from all campaigns
                            squadronInfo.totalCredits = creditsData.data.reduce((sum, c) => sum + (c.credits || 0), 0);
                            squadronInfo.creditsByCampaign = creditsData.data;
                        }
                    }
                } catch (error) {
                    console.warn(`Failed to load credits for squadron ${squadron.name}:`, error);
                }
                
                squadronData.push(squadronInfo);
            }
            
            return { 
                squadrons: squadronData
            };
            
        } catch (error) {
            console.error('Error loading squadron data:', error);
            // Try to get more details about the error
            if (error.message === 'Failed to load squadrons') {
                // The squadrons endpoint failed, let's check the response
                try {
                    const errorResp = await fetch(buildUrl('get_squadrons.php'));
                    const errorText = await errorResp.text();
                    console.error('Squadrons endpoint response:', errorText);
                    
                    // Try to parse as JSON to get error details
                    try {
                        const errorData = JSON.parse(errorText);
                        if (errorData.error) {
                            throw new Error(errorData.error);
                        }
                    } catch (e) {
                        // Not JSON, probably PHP error
                        throw new Error('API error: ' + errorText.substring(0, 200));
                    }
                } catch (e) {
                    console.error('Failed to get error details:', e);
                }
            }
            throw error;
        }
    }
    
    // Main execution
    loadSquadronData().then(({ squadrons }) => {

        const squadronBody = document.querySelector('#squadronsTable tbody');
        const membersBody = document.querySelector('#membersTable tbody');
        const leaderboardBody = document.querySelector('#leaderboardTable tbody');

        const medals = ['🥇', '🥈', '🥉'];

        function renderTables(filter = '') {
            if (squadronBody) squadronBody.innerHTML = '';
            if (membersBody) membersBody.innerHTML = '';
            if (leaderboardBody) leaderboardBody.innerHTML = '';

            // === Squadrons Table ===
            if (squadronBody) {
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
            }

            // === Members Table ===
            if (membersBody) {
                squadrons.forEach((sq, index) => {
                const groupId = "group-" + index;
                const lowerName = sq.name.toLowerCase();
                const matchFound = lowerName.includes(filter) || sq.members.some(m => m.name.toLowerCase().includes(filter));
                if (!matchFound) return;

                const headerRow = document.createElement('tr');
                headerRow.classList.add('toggle-header');
                headerRow.style.cursor = "pointer";
                headerRow.innerHTML = `
                    <td><img src="${escapeHtml(sq.image_url || '')}" alt="${escapeHtml(sq.name || '')}" style="width: 60px;"></td>
                    <td>${escapeHtml(sq.name || '')} (${sq.member_count || 0} members)</td>
                    <td><em>Click to show/hide members</em></td>
                `;
                membersBody.appendChild(headerRow);

                sq.members.forEach(member => {
                    if (!member.name.toLowerCase().includes(filter) && !lowerName.includes(filter)) return;

                    const row = document.createElement('tr');
                    row.classList.add(groupId);
                    row.style.display = "none";
                    row.style.cursor = "pointer";
                    row.innerHTML = `
                        <td></td>
                        <td></td>
                        <td class="member-name" data-pilot="${escapeHtml(member.name || '')}">
                            <a href="pilot_statistics.php?search=${encodeURIComponent(member.name || '')}" style="color: inherit; text-decoration: none;">
                                ${escapeHtml(member.name || '')} <small>(Last seen: ${new Date(member.date).toLocaleDateString()})</small>
                            </a>
                        </td>
                    `;
                    
                    // Add hover effect
                    row.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                    });
                    row.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = '';
                    });
                    
                    // Add click handler for the entire row
                    row.addEventListener('click', function(e) {
                        // Don't navigate if clicking on the header row
                        if (!e.target.closest('.toggle-header')) {
                            window.location.href = `pilot_statistics.php?search=${encodeURIComponent(member.name || '')}`;
                        }
                    });
                    
                    membersBody.appendChild(row);
                });

                headerRow.addEventListener('click', () => {
                    const groupRows = document.querySelectorAll('.' + groupId);
                    const isExpanded = groupRows[0] && groupRows[0].style.display !== 'none';
                    groupRows.forEach(r => {
                        r.style.display = isExpanded ? 'none' : 'table-row';
                    });
                    headerRow.classList.toggle('expanded', !isExpanded);
                });
                });
            }

            // === Leaderboard Table ===
            if (leaderboardBody) {
                squadrons
                .filter(sq => sq.name.toLowerCase().includes(filter))
                .sort((a, b) => b.totalCredits - a.totalCredits)
                .forEach((squadron, index) => {
                    const medal = medals[index] || '';
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><img src="${escapeHtml(squadron.image_url || '')}" alt="${escapeHtml(squadron.name || '')}" style="width: 60px;"></td>
                        <td>${medal} ${escapeHtml(squadron.name || '')}</td>
                        <td>${escapeHtml(String(squadron.totalCredits || 0))}</td>
                    `;
                    leaderboardBody.appendChild(row);
                });
            }
        }

        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase().trim();
            renderTables(filter);
        });

        renderTables(); // Initial load
    }).catch(err => {
        console.error('Error loading squadron data:', err);
        document.querySelector('main').innerHTML = `
            <div class="alert" style="text-align: center; padding: 50px;">
                <h2>Error Loading Squadron Data</h2>
                <p>${err.message}</p>
                <p>Please check your configuration and try again.</p>
            </div>
        `;
    });
});
</script>

<?php
include 'footer.php';
?>
