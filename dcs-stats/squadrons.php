<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php';
require_once __DIR__ . '/site_features.php';
require_once __DIR__ . '/table-responsive.php';
require_once __DIR__ . '/language.php';
include 'nav.php';

if (!isFeatureEnabled('squadrons_enabled')):
?>
<main>
    <div class="alert" style="text-align: center; padding: 50px;">
        <h2><?php echo htmlspecialchars(dcs_t('squadrons.disabled_title')); ?></h2>
        <p><?php echo htmlspecialchars(dcs_t('squadrons.disabled_message')); ?></p>
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
            padding: 0 15px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 100%;
        }
        
        #searchInput {
            width: 100%;
            max-width: 100%;
            padding: 12px 15px;
            font-size: 16px; /* Prevents zoom on iOS */
            box-sizing: border-box;
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
        box-sizing: border-box;
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
        box-sizing: border-box;
    }
    
    #searchInput:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
    }
    
    #searchInput::placeholder {
        color: #999;
    }
    
    /* Mobile-specific squadron card styles */
    @media screen and (max-width: 768px) {
        /* Squadron members mobile card */
        .squadron-members-card {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        .squadron-members-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            cursor: pointer;
            position: relative;
        }
        
        .squadron-members-info {
            flex: 1;
            min-width: 0;
        }
        
        .squadron-members-count {
            font-size: 0.85rem;
            color: #999;
            margin-top: 3px;
        }
        
        .expand-indicator {
            font-size: 1.2rem;
            color: #4CAF50;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }
        
        .squadron-members-list {
            border-top: 1px solid rgba(76, 175, 80, 0.2);
            max-height: 300px;
            overflow-y: auto;
        }
        
        .member-item {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .member-item:active {
            background: rgba(76, 175, 80, 0.1);
        }
        
        .member-name {
            color: #fff;
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .member-date {
            font-size: 0.8rem;
            color: #999;
        }
        
        /* Leaderboard mobile card */
        .leaderboard-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            margin-bottom: 15px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 12px;
        }
        
        .leaderboard-card-rank {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4CAF50;
            min-width: 50px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .leaderboard-card-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }
        
        .leaderboard-card-info {
            flex: 1;
            min-width: 0;
        }
        
        .squadron-credits {
            font-size: 0.9rem;
            color: #999;
            margin-top: 3px;
        }
        
        /* Ensure squadron logos don't overflow */
        .squadron-card-logo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(76, 175, 80, 0.3);
            flex-shrink: 0;
        }
        
        /* Fix squadron name overflow */
        .squadron-card-name {
            font-size: 1.1rem;
            color: #4CAF50;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .squadron-placeholder {
            background: rgba(76, 175, 80, 0.1);
            border: 1px dashed rgba(76, 175, 80, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            font-size: 24px;
            color: rgba(76, 175, 80, 0.5);
        }

    }
</style>

<?php tableResponsiveStyles(); ?>

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

<script>
document.addEventListener("DOMContentLoaded", () => {
    const i18n = <?php echo json_encode([
        'members' => dcs_t('squadrons.members'),
        'clickToggle' => dcs_t('squadrons.click_toggle'),
        'lastSeen' => dcs_t('squadrons.last_seen'),
        'unknown' => dcs_t('squadrons.unknown'),
        'credits' => dcs_t('squadrons.credits'),
        'loadFailed' => dcs_t('squadrons.load_failed'),
        'errorTitle' => dcs_t('squadrons.error_title'),
        'configRetry' => dcs_t('squadrons.config_retry')
    ], JSON_UNESCAPED_UNICODE); ?>;
    const searchInput = document.getElementById('searchInput');

    // Helper to build URLs
    const basePath = window.DCS_CONFIG ? window.DCS_CONFIG.basePath : '';
    const buildUrl = (path) => basePath ? `${basePath}/${path}` : path;

    function toArray(value) {
        if (Array.isArray(value)) return value;
        if (!value || typeof value !== 'object') return [];

        if (Array.isArray(value.data)) return value.data;
        if (Array.isArray(value.members)) return value.members;
        if (Array.isArray(value.items)) return value.items;

        return Object.values(value).filter(item => item && typeof item === 'object');
    }

    function normalizeMember(member) {
        if (typeof member === 'string') {
            return { nick: member, name: member, date: null };
        }

        const nick = member?.nick || member?.name || member?.player_name || member?.display_name || '';
        return {
            ...member,
            nick: String(nick),
            name: String(member?.name || nick),
            date: member?.date || member?.last_seen || member?.lastSeen || null
        };
    }

    function normalizeSquadron(squadron) {
        const rawMembers = toArray(squadron?.members);
        const members = rawMembers.map(normalizeMember);
        const memberCount = Number.isFinite(Number(squadron?.member_count))
            ? Number(squadron.member_count)
            : (members.length || Number(squadron?.members) || 0);

        return {
            ...squadron,
            name: String(squadron?.name || ''),
            description: String(squadron?.description || ''),
            image_url: String(squadron?.image_url || ''),
            members,
            member_count: memberCount,
            totalCredits: Number(squadron?.totalCredits || squadron?.total_credits || 0)
        };
    }

    function formatMemberDate(dateValue) {
        if (!dateValue) return i18n.unknown;
        const date = new Date(dateValue);
        return Number.isNaN(date.getTime()) ? i18n.unknown : date.toLocaleDateString();
    }
    
    // Load squadron data from API
    async function loadSquadronData() {
        try {
            // First, get the list of squadrons
            const squadronsResponse = await fetch(buildUrl('get_squadrons.php'));
            if (!squadronsResponse.ok) {
                throw new Error(i18n.loadFailed);
            }
            const squadronsData = await squadronsResponse.json();
            const squadrons = toArray(squadronsData.data || squadronsData).map(normalizeSquadron);
            
            // No need to load players separately - member names come from API
            
            // Load member and credit data for each squadron
            const squadronData = [];
            
            for (const squadron of squadrons) {
                const squadronInfo = {
                    ...squadron,
                    members: squadron.members || [],
                    totalCredits: squadron.totalCredits || 0
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
                            const members = toArray(membersData.data).map(normalizeMember);
                            squadronInfo.members = members;
                            squadronInfo.member_count = members.length;
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
                        if (creditsData.data) {
                            // Sum up credits from all campaigns
                            squadronInfo.totalCredits = creditsData.data.credits || 0;
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
            if (error.message === i18n.loadFailed) {
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
            // Get mobile card containers
            const squadronsCards = document.querySelector('#squadronsCards');
            const membersCards = document.querySelector('#membersCards');
            const leaderboardCards = document.querySelector('#leaderboardCards');
            
            // Clear all content
            if (squadronBody) squadronBody.innerHTML = '';
            if (membersBody) membersBody.innerHTML = '';
            if (leaderboardBody) leaderboardBody.innerHTML = '';
            if (squadronsCards) squadronsCards.innerHTML = '';
            if (membersCards) membersCards.innerHTML = '';
            if (leaderboardCards) leaderboardCards.innerHTML = '';

            // === Squadrons Table ===
            if (squadronBody) {
                squadrons.forEach(sq => {
                    const squadronName = String(sq.name || '');
                    const squadronDescription = String(sq.description || '');
                    if (!squadronName.toLowerCase().includes(filter) && !squadronDescription.toLowerCase().includes(filter)) return;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            ${sq.image_url ?
                                `<img src="${escapeHtml(sq.image_url)}" alt="${escapeHtml(sq.name || '')}" style="width: 80px;">` :
                                `<div class="squadron-placeholder">⚡</div>`
                            }
                        </td>
                        <td>${escapeHtml(sq.name || '')}</td>
                        <td>${escapeHtml(sq.description || '')}</td>
                    `;
                    squadronBody.appendChild(row);
                    
                    // Create mobile card for squadron
                    if (squadronsCards) {
                        const card = document.createElement('div');
                        card.className = 'mobile-card squadron-card';
                        card.innerHTML = `
                            <div class="squadron-card-header">
                                ${sq.image_url ?
                                    `<img src="${escapeHtml(sq.image_url)}" alt="${escapeHtml(sq.name || '')}" class="squadron-card-logo">` :
                                    `<div class="squadron-placeholder squadron-card-logo">⚡</div>`
                                }
                                <div class="squadron-card-info">
                                    <div class="squadron-card-name">${escapeHtml(sq.name || '')}</div>
                                    <div class="squadron-card-description">${escapeHtml(sq.description || '')}</div>
                                </div>
                            </div>
                            <div class="squadron-card-members">${sq.member_count || 0} ${escapeHtml(i18n.members)}</div>
                        `;
                        squadronsCards.appendChild(card);
                    }
                });
            }

            // === Members Table ===
            if (membersBody) {
                // Clear mobile cards for members
                if (membersCards) membersCards.innerHTML = '';
                squadrons.forEach((sq, index) => {
                const groupId = "group-" + index;
                const members = Array.isArray(sq.members) ? sq.members : [];
                const lowerName = String(sq.name || '').toLowerCase();
                const matchFound = lowerName.includes(filter) || members.some(m => String(m.nick || '').toLowerCase().includes(filter));
                if (!matchFound) return;

                const headerRow = document.createElement('tr');
                headerRow.classList.add('toggle-header');
                headerRow.style.cursor = "pointer";
                headerRow.innerHTML = `
                    <td>
                        ${sq.image_url ?
                            `<img src="${escapeHtml(sq.image_url)}" alt="${escapeHtml(sq.name || '')}" style="width: 60px;">` :
                            `<div class="squadron-placeholder" style="width: 60px; height: 60px;">⚡</div>`
                        }
                    </td>
                    <td>${escapeHtml(sq.name || '')} (${sq.member_count || 0} ${escapeHtml(i18n.members)})</td>
                    <td><em>${escapeHtml(i18n.clickToggle)}</em></td>
                `;
                membersBody.appendChild(headerRow);

                members.forEach(member => {
                    if (!String(member.nick || '').toLowerCase().includes(filter) && !lowerName.includes(filter)) return;

                    const row = document.createElement('tr');
                    row.classList.add(groupId);
                    row.style.display = "none";
                    row.style.cursor = "pointer";
                    row.innerHTML = `
                        <td></td>
                        <td></td>
                        <td class="member-name" data-pilot="${escapeHtml(member.name || '')}">
                            <a href="pilot_statistics.php?search=${encodeURIComponent(member.nick || '')}" style="color: inherit; text-decoration: none;">
                                ${escapeHtml(member.nick || '')} <small>(${escapeHtml(i18n.lastSeen)}: ${formatMemberDate(member.date)})</small>
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
                            window.location.href = `pilot_statistics.php?search=${encodeURIComponent(member.nick || '')}`;
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
                
                // Create mobile card for squadron members
                if (membersCards) {
                    const squadCard = document.createElement('div');
                    squadCard.className = 'mobile-card squadron-members-card';
                    squadCard.innerHTML = `
                        <div class="squadron-members-header" onclick="toggleMobileMembers('${groupId}')">
                            <img src="${escapeHtml(sq.image_url || '')}" alt="${escapeHtml(sq.name || '')}" class="squadron-card-logo">
                            <div class="squadron-members-info">
                                <div class="squadron-card-name">${escapeHtml(sq.name || '')}</div>
                                <div class="squadron-members-count">${sq.member_count || 0} ${escapeHtml(i18n.members)}</div>
                            </div>
                            <div class="expand-indicator" id="expand-${groupId}">▼</div>
                        </div>
                        <div class="squadron-members-list" id="members-${groupId}" style="display: none;">
                            ${members.map(member => `
                                <div class="member-item" onclick="window.location.href='pilot_statistics.php?search=${encodeURIComponent(member.nick || '')}'">
                                    <div class="member-name">${escapeHtml(member.nick || '')}</div>
                                    <div class="member-date">${escapeHtml(i18n.lastSeen)}: ${formatMemberDate(member.date)}</div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    membersCards.appendChild(squadCard);
                }
                });
            }

            // === Leaderboard Table ===
            if (leaderboardBody) {
                // Clear mobile cards for leaderboard
                if (leaderboardCards) leaderboardCards.innerHTML = '';
                squadrons
                    .filter(sq => String(sq.name || '').toLowerCase().includes(filter))
                    .sort((a, b) => b.totalCredits - a.totalCredits)
                    .forEach((squadron, index) => {
                        const medal = medals[index] || '';
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>
                                ${squadron.image_url ?
                                    `<img src="${escapeHtml(squadron.image_url)}" alt="${escapeHtml(squadron.name || '')}" style="width: 60px;">` :
                                    `<div class="squadron-placeholder" style="width: 60px; height: 60px;">⚡</div>`
                                }
                            </td>
                            <td>${medal} ${escapeHtml(squadron.name || '')}</td>
                            <td>${escapeHtml(String(squadron.totalCredits || 0))}</td>
                        `;
                        leaderboardBody.appendChild(row);

                        // Mobile card handling
                        if (leaderboardCards) {
                            const card = document.createElement('div');
                            card.className = 'mobile-card leaderboard-card';
                            card.innerHTML = `
                                <div class="leaderboard-card-rank">${medal || `#${index + 1}`}</div>
                                <div class="leaderboard-card-content">
                                    ${squadron.image_url ?
                                        `<img src="${escapeHtml(squadron.image_url)}" alt="${escapeHtml(squadron.name || '')}" class="squadron-card-logo">` :
                                        `<div class="squadron-placeholder squadron-card-logo">⚡</div>`
                                    }
                                    <div class="leaderboard-card-info">
                                        <div class="squadron-card-name">${escapeHtml(squadron.name || '')}</div>
                                        <div class="squadron-credits">${escapeHtml(String(squadron.totalCredits || 0))} ${escapeHtml(i18n.credits)}</div>
                                    </div>
                                </div>
                            `;
                            leaderboardCards.appendChild(card);
                        }
                    });
            }
        }

        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase().trim();
            renderTables(filter);
        });

        renderTables(); // Initial load
        
        // Add toggle function for mobile members
        window.toggleMobileMembers = function(groupId) {
            const membersList = document.getElementById('members-' + groupId);
            const indicator = document.getElementById('expand-' + groupId);
            if (membersList) {
                const isVisible = membersList.style.display !== 'none';
                membersList.style.display = isVisible ? 'none' : 'block';
                if (indicator) {
                    indicator.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
        };
    }).catch(err => {
        console.error('Error loading squadron data:', err);
        document.querySelector('main').innerHTML = `
            <div class="alert" style="text-align: center; padding: 50px;">
                <h2>${escapeHtml(i18n.errorTitle)}</h2>
                <p>${err.message}</p>
                <p>${escapeHtml(i18n.configRetry)}</p>
            </div>
        `;
    });
});
</script>

<?php
include 'footer.php';
?>
