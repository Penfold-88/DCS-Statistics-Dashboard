document.addEventListener("DOMContentLoaded", () => {
    const i18n = window.DCS_SQUADRONS_CONFIG?.i18n || {};
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
