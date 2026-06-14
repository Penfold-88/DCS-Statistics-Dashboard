const pilotStatisticsConfig = window.DCS_PILOT_STATISTICS_CONFIG || {};
const i18n = pilotStatisticsConfig.i18n || {};
const siteFeatures = pilotStatisticsConfig.siteFeatures || {};
const publicDateFormat = pilotStatisticsConfig.publicDateFormat || 'd/m/Y';

// Function to create stat items dynamically
function createStatItem(label, value, id) {
    return `
        <div class="stat-item">
            <span class="stat-label">${label}:</span>
            <span class="stat-value" id="${id}">${value}</span>
        </div>
    `;
}

// Function to populate stats based on available data and enabled features
function populateStatsGrid(stats) {
    // Combat stats grid
    const combatGrid = document.getElementById('combat-stats-grid');
    const combatGroup = combatGrid.parentElement.parentElement; // .stat-group
    combatGrid.innerHTML = '';
    let hasCombatStats = false;
    
    // Show combat stats if enabled and data exists
    if (siteFeatures.pilot_combat_stats) {
        if (stats.kills !== undefined) {
            combatGrid.innerHTML += createStatItem(i18n.kills, stats.kills || 0, 'pilot-kills');
            hasCombatStats = true;
        }
        if (stats.deaths !== undefined) {
            combatGrid.innerHTML += createStatItem(i18n.deaths, stats.deaths || 0, 'pilot-deaths');
            hasCombatStats = true;
        }
        if (stats.kd_ratio !== undefined) {
            combatGrid.innerHTML += createStatItem(i18n.kdRatio, (stats.kd_ratio || 0).toFixed(2), 'pilot-kd');
            hasCombatStats = true;
        }
    }
    
    // Show flight stats if enabled and data exists
    if (siteFeatures.pilot_flight_stats) {
        if (stats.takeoffs !== undefined) {
            combatGrid.innerHTML += createStatItem(i18n.takeoffs, stats.takeoffs || 0, 'pilot-takeoffs');
            hasCombatStats = true;
        }
        if (stats.landings !== undefined) {
            combatGrid.innerHTML += createStatItem(i18n.landings, stats.landings || 0, 'pilot-landings');
            hasCombatStats = true;
        }
        if (stats.crashes !== undefined) {
            combatGrid.innerHTML += createStatItem(i18n.crashes, stats.crashes || 0, 'pilot-crashes');
            hasCombatStats = true;
        }
        if (stats.ejections !== undefined) {
            combatGrid.innerHTML += createStatItem(i18n.ejections, stats.ejections || 0, 'pilot-ejections');
            hasCombatStats = true;
        }
    }
    
    // Hide entire combat stats group if no stats to show
    combatGroup.style.display = hasCombatStats ? 'block' : 'none';
    
    // Secondary stats grid
    const secondaryGrid = document.getElementById('secondary-stats-grid');
    secondaryGrid.innerHTML = '';
    let hasSecondaryStats = false;
    
    // Credits if enabled
    if (siteFeatures.credits && stats.credits !== undefined) {
        secondaryGrid.innerHTML += createStatItem(i18n.credits, stats.credits || 0, 'pilot-credits');
        hasSecondaryStats = true;
    }
    
    // Aircraft if we have data
    if (stats.most_used_aircraft && stats.most_used_aircraft !== 'N/A') {
        secondaryGrid.innerHTML += createStatItem(i18n.mostUsedAircraft, stats.most_used_aircraft, 'pilot-aircraft');
        hasSecondaryStats = true;
    }
    
    // Squadron if enabled
    if (siteFeatures.squadrons && stats.squadron) {
        const squadronHtml = `
            <div class="stat-item" id="squadron-info">
                <span class="stat-label">${escapeHtml(i18n.squadron)}:</span>
                <span class="stat-value" id="pilot-squadron">${stats.squadron}</span>
            </div>
        `;
        secondaryGrid.innerHTML += squadronHtml;
        hasSecondaryStats = true;
    }
    
    // Show/hide secondary stats group
    document.getElementById('secondary-stats-group').style.display = hasSecondaryStats ? 'block' : 'none';
    
    // Session stats grid
    const sessionGrid = document.getElementById('session-stats-grid');
    sessionGrid.innerHTML = '';
    let hasSessionStats = false;
    
    if (siteFeatures.pilot_session_stats && (stats.last_session_kills !== undefined || stats.last_session_deaths !== undefined)) {
        if (stats.last_session_kills !== undefined) {
            sessionGrid.innerHTML += createStatItem(i18n.sessionKills, stats.last_session_kills || 0, 'pilot-session-kills');
            hasSessionStats = true;
        }
        if (stats.last_session_deaths !== undefined) {
            sessionGrid.innerHTML += createStatItem(i18n.sessionDeaths, stats.last_session_deaths || 0, 'pilot-session-deaths');
            hasSessionStats = true;
        }
    }
    
    // Show/hide session stats group
    document.getElementById('session-stats-group').style.display = hasSessionStats ? 'block' : 'none';
}

async function searchForPlayers() {
    const searchInput = document.getElementById('playerSearchInput');
    const searchTerm = searchInput.value.trim();
    
    if (!searchTerm) {
        alert(i18n.enterName);
        return;
    }
    
    
    // Hide all sections
    document.getElementById('search-results').style.display = 'none';
    document.getElementById('multiple-results').style.display = 'none';
    document.getElementById('no-results').style.display = 'none';
    document.getElementById('loading').style.display = 'block';
    
    try {
        // Search for players using client-side API
        const searchData = await window.dcsAPI.searchPlayers(searchTerm);
        
        
        document.getElementById('loading').style.display = 'none';
        
        if (searchData.error || searchData.count === 0) {
            let errorMessage = searchData.error || `${i18n.noMatches.replace('{search}', searchTerm)}\n• ${i18n.checkSpelling}\n• ${i18n.usePartial}\n• ${i18n.searchStart}`;
            if (searchData.message) {
                errorMessage += '\n\n' + searchData.message;
            }
            document.getElementById('no-results-message').innerHTML = errorMessage.replace(/\n/g, '<br>');
            document.getElementById('no-results').style.display = 'block';
            return;
        }
        
        if (searchData.count === 1) {
            // Single result - load directly
            await loadPilotStats(searchData.results[0]);
        } else {
            // Multiple results - show selection
            showMultipleResults(searchData.results);
        }
        
    } catch (error) {
        console.error('Error searching for pilots:', error);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('no-results-message').textContent = i18n.searchError.replace('{error}', error.message);
        document.getElementById('no-results').style.display = 'block';
    }
}

function showMultipleResults(results) {
    const resultsList = document.getElementById('results-list');
    resultsList.innerHTML = '';
    
    results.forEach(pilot => {
        const resultItem = document.createElement('div');
        resultItem.className = 'result-item';
        resultItem.textContent = pilot.nick;
        resultItem.onclick = () => {
            document.getElementById('multiple-results').style.display = 'none';
            document.getElementById('loading').style.display = 'block';
            loadPilotStats(pilot);
        };
        resultsList.appendChild(resultItem);
    });
    
    document.getElementById('multiple-results').style.display = 'block';
}

async function loadPilotStats(player) {
    document.getElementById('multiple-results').style.display = 'none';
    
    try {
        
        // Get player stats using client-side API
        const statsResult = await window.dcsAPI.getPlayerStats(player.nick, player.date);
        
        if (statsResult.error) {
            document.getElementById('loading').style.display = 'none';
            
            // Show more detailed error message
            let errorMessage = statsResult.error || i18n.noPilotFound;
            if (statsResult.message) {
                errorMessage += '<br><br>' + statsResult.message;
            }
            
            document.getElementById('no-results-message').innerHTML = errorMessage;
            document.getElementById('no-results').style.display = 'block';
            return;
        }
        
        // Extract actual stats data from the response
        const statsData = statsResult.data || statsResult;
        
        // If statsData doesn't have the expected structure, use the raw result
        const finalStats = statsData.data || statsData;
        
        // Get credits data for this specific player if credits are enabled
        let credits = undefined;
        if (siteFeatures.credits) {
            try {
                // Get API config
                const config = await window.dcsAPI.loadConfig();
                if (config.use_api) {
                    // Call credits endpoint with player name and current date
                    const basePath = window.DCS_CONFIG ? window.DCS_CONFIG.basePath : '';
                    const buildUrl = (path) => basePath ? `${basePath}/${path}` : path;
                    const response = await fetch(buildUrl('api_proxy.php?endpoint=' + encodeURIComponent('/credits') + '&method=POST'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            nick: player.nick,
                            date: player.date
                        })
                    });
                    
                    if (response.ok) {
                        const creditsData = await response.json();
                        credits = creditsData.credits;
                    } else {
                        credits = 0
                    }
                }
            } catch (e) {
                console.warn('Could not load credits data:', e);
                credits = 0;
            }
        }
        
        // Get squadron data - simplified approach
        let squadron = i18n.none;
        let squadronLogo = null;
        
        // buildUrl should already be defined from earlier in the code
        const basePath = window.DCS_CONFIG ? window.DCS_CONFIG.basePath : '';
        const buildUrl = (path) => basePath ? `${basePath}/${path}` : path;
        
        // Create a helper function to find the pilot's squadron
        async function findPilotSquadron(pilotName) {
            try {
                // Get all squadrons
                const squadronsResp = await fetch(buildUrl('get_squadrons.php'));
                if (!squadronsResp.ok) {
                    console.error('Failed to fetch squadrons');
                    return null;
                }
                
                const squadronsData = await squadronsResp.json();
                
                if (!squadronsData.data || !Array.isArray(squadronsData.data)) {
                    console.error('Invalid squadrons data format');
                    return null;
                }
                
                
                // Check each squadron's members
                for (const squadron of squadronsData.data) {
                    
                    const membersResp = await fetch(buildUrl('get_squadron_members.php'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ name: squadron.name })
                    });
                    
                    if (membersResp.ok) {
                        const membersData = await membersResp.json();
                        
                        if (membersData.data && Array.isArray(membersData.data)) {
                            // Log each member comparison
                            for (const member of membersData.data) {
                                if (member.nick.toLowerCase() === pilotName.toLowerCase()) {
                                    return {
                                        name: squadron.name,
                                        logo: squadron.image_url
                                    };
                                }
                            }
                        }
                    } else {
                        console.error(`Failed to fetch members for ${squadron.name}`);
                    }
                }
                
                return null;
            } catch (e) {
                console.error('Squadron lookup error:', e);
                return null;
            }
        }
        
        // Try to find squadron for the pilot
        const squadronInfo = await findPilotSquadron(player.nick);
        if (squadronInfo) {
            squadron = squadronInfo.name;
            squadronLogo = squadronInfo.logo;
        }
        
        // Helper function to safely update element text
        function updateElement(id, value) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            } else {
                console.warn(`Element with id '${id}' not found`);
            }
        }
        
        // Update pilot name
        updateElement('pilot-name', player.nick);
        
        // Prepare stats object with all available data
        const displayStats = {
            ...finalStats,
            credits: credits,
            squadron: squadron
        };
        
        // Dynamically populate stats grids based on available data
        populateStatsGrid(displayStats);
        
        // Check if pilot card has any visible content
        const combatGroup = document.getElementById('combat-stats-group');
        const secondaryGroup = document.getElementById('secondary-stats-group');
        const sessionGroup = document.getElementById('session-stats-group');
        
        const hasAnyStats = (combatGroup && combatGroup.style.display !== 'none') ||
                           (secondaryGroup && secondaryGroup.style.display !== 'none') ||
                           (sessionGroup && sessionGroup.style.display !== 'none');
        
        // If no stats are visible, show a message
        if (!hasAnyStats) {
            const pilotCard = document.getElementById('pilot-card');
            pilotCard.innerHTML = `
                <h3 id="pilot-name">${escapeHtml(player.nick)}</h3>
                <div class="no-stats-message">
                    <p>${escapeHtml(i18n.noStatsEnabled)}</p>
                    <p>${escapeHtml(i18n.contactAdmin)}</p>
                </div>
            `;
            
            // Also hide the charts container if no stats are shown
            const chartsContainer = document.querySelector('.charts-container');
            if (chartsContainer) {
                chartsContainer.style.display = 'none';
            }
        } else {
            // Check if any charts are visible, if not hide the container
            const visibleCharts = document.querySelectorAll('.chart-wrapper:not([style*="display: none"])');
            const chartsContainer = document.querySelector('.charts-container');
            if (chartsContainer && visibleCharts.length === 0) {
                chartsContainer.style.display = 'none';
            }
        }
        
        // Update squadron info with logo if available (only if element exists)
        const squadronInfoDiv = document.getElementById('squadron-info');
        if (squadronInfoDiv) {
            if (squadronLogo && squadron !== i18n.none && squadron !== 'N/A') {
                squadronInfoDiv.innerHTML = `
                    <span class="stat-label">${escapeHtml(i18n.squadron)}:</span>
                    <div class="squadron-display">
                        <img src="${squadronLogo}" alt="${squadron}" class="squadron-logo">
                        <span class="stat-value">${squadron}</span>
                    </div>
                `;
            } else {
                const squadronElement = document.getElementById('pilot-squadron');
                if (squadronElement) {
                    squadronElement.textContent = squadron;
                }
            }
        }
        
        // Show results
        document.getElementById('loading').style.display = 'none';
        document.getElementById('search-results').style.display = 'block';

        loadCarrierTraps(player);
        
        // Create charts based on enabled features
        if (siteFeatures.pilot_combat_stats) {
            createCombatChart(finalStats);
        } else {
            // Hide combat chart if feature disabled
            const combatChartWrapper = document.querySelector('.chart-wrapper[title*="combat"]');
            if (combatChartWrapper) {
                combatChartWrapper.style.display = 'none';
            }
        }
        
        if (siteFeatures.pilot_flight_stats) {
            createFlightChart(finalStats);
        } else {
            // Hide flight chart if feature disabled
            const flightChartWrapper = document.querySelector('.chart-wrapper[title*="flight"]');
            if (flightChartWrapper) {
                flightChartWrapper.style.display = 'none';
            }
        }
        
        // Check for aircraft usage data only if feature is enabled
        if (siteFeatures.pilot_aircraft_chart) {
            if (finalStats.aircraftUsage && finalStats.aircraftUsage.length > 0) {
                createAircraftChart(finalStats.aircraftUsage);
            } else if (finalStats.kills_by_module && Object.keys(finalStats.kills_by_module).length > 0) {
                // Convert kills_by_module from API format to aircraftUsage format
                const aircraftUsage = Object.entries(finalStats.kills_by_module).map(([name, count]) => ({
                    name,
                    count
                })).sort((a, b) => b.count - a.count).slice(0, 5); // Top 5 aircraft
                if (aircraftUsage.length > 0) {
                    createAircraftChart(aircraftUsage);
                }
            }
        } else {
            // Hide aircraft chart if feature disabled
            const aircraftChartWrapper = document.getElementById('aircraftChartWrapper');
            if (aircraftChartWrapper) {
                aircraftChartWrapper.style.display = 'none';
            }
        }
        
        // Trap scores removed - not available in API
        
    } catch (error) {
        console.error('Error loading pilot stats:', error);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('no-results-message').innerHTML = `${escapeHtml(i18n.loadError.replace('{error}', error.message))}<br><br>${escapeHtml(i18n.consoleDetails)}`;
        document.getElementById('no-results').style.display = 'block';
    }
}

// Chart instances
let combatChart = null;
let flightChart = null;
let aircraftChart = null;
let trapScoresChart = null;

function cssThemeValue(name, fallback) {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return value || fallback;
}

function firstValue(source, keys, fallback = '') {
    for (const key of keys) {
        if (source && source[key] !== undefined && source[key] !== null && source[key] !== '') {
            return source[key];
        }
    }
    return fallback;
}

function normaliseTrap(trap) {
    return {
        grade: firstValue(trap, ['grade', 'Grade', 'lso_grade'], '-'),
        points: firstValue(trap, ['points', 'Points', 'score'], '-'),
        wire: firstValue(trap, ['wire', 'Wire'], '-'),
        aircraft: firstValue(trap, ['unit_type', 'aircraft', 'Aircraft', 'module', 'unit'], '-'),
        caseType: firstValue(trap, ['trapcase', 'case', 'Case'], '-'),
        location: firstValue(trap, ['place', 'location', 'carrier'], '-'),
        comment: firstValue(trap, ['comment', 'details'], ''),
        time: firstValue(trap, ['time', 'date'], '-'),
        night: firstValue(trap, ['night'], null)
    };
}

function formatTrapTime(value) {
    if (!value || value === '-') {
        return '-';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return date.toLocaleString();
}

function stripTrapDatePadding(value) {
    return String(value)
        .replace(/\b0(\d)(?=[\/.-])/g, '$1')
        .replace(/([\/.-])0(\d)\b/g, '$1$2');
}

function formatTrapDateNoPadding(date) {
    const day = date.getDate();
    const month = date.getMonth() + 1;
    const year = date.getFullYear();

    switch (publicDateFormat) {
        case 'm/d/Y':
            return `${month}/${day}/${year}`;
        case 'Y-m-d':
            return `${year}-${month}-${day}`;
        case 'd-m-Y':
            return `${day}-${month}-${year}`;
        case 'm-d-Y':
            return `${month}-${day}-${year}`;
        case 'Y/m/d':
            return `${year}/${month}/${day}`;
        case 'd/m/Y':
        default:
            return `${day}/${month}/${year}`;
    }
}

function formatTrapDateTime(value) {
    if (!value || value === '-') {
        return { date: '-', time: '' };
    }

    const date = new Date(value);
    if (!Number.isNaN(date.getTime())) {
        return {
            date: formatTrapDateNoPadding(date),
            time: date.toLocaleTimeString()
        };
    }

    const raw = String(value).trim();
    const commaParts = raw.split(',');
    if (commaParts.length >= 2) {
        return {
            date: stripTrapDatePadding(commaParts[0].trim()),
            time: commaParts.slice(1).join(',').trim()
        };
    }

    const isoParts = raw.split(/[T ]/);
    if (isoParts.length >= 2) {
        return {
            date: stripTrapDatePadding(isoParts[0].trim()),
            time: isoParts.slice(1).join(' ').replace(/Z$/, '').trim()
        };
    }

    return { date: stripTrapDatePadding(raw), time: '' };
}

async function loadCarrierTraps(player) {
    if (!siteFeatures.pilot_carrier_traps || !window.dcsAPI?.getPilotTraps) return;

    const group = document.getElementById('carrier-traps-group');
    const status = document.getElementById('carrier-traps-status');
    const summary = document.getElementById('carrier-traps-summary');
    const tableWrap = document.getElementById('carrier-traps-table-wrap');
    const tableBody = document.getElementById('carrier-traps-table-body');
    if (!group || !status || !summary || !tableWrap || !tableBody) return;

    group.style.display = 'block';
    status.textContent = i18n.loadingTraps;
    summary.innerHTML = '';
    tableBody.innerHTML = '';
    tableWrap.style.display = 'none';

    try {
        const traps = await window.dcsAPI.getPilotTraps(player.nick, null, { limit: 10 });
        if (!Array.isArray(traps) || traps.length === 0) {
            status.textContent = i18n.noTrapData;
            return;
        }

        const normalised = traps.map(normaliseTrap);
        const latest = normalised[0];
        status.textContent = '';
        summary.innerHTML = [
            createStatItem(i18n.trapLatestGrade, escapeHtml(String(latest.grade)), 'trap-latest-grade'),
            createStatItem(i18n.trapLatestPoints, escapeHtml(String(latest.points)), 'trap-latest-points'),
            createStatItem(i18n.trapLatestWire, escapeHtml(String(latest.wire)), 'trap-latest-wire'),
            createStatItem(i18n.trapLatestAircraft, escapeHtml(String(latest.aircraft)), 'trap-latest-aircraft'),
            createStatItem(i18n.trapLatestCase, escapeHtml(String(latest.caseType)), 'trap-latest-case'),
            createStatItem(i18n.trapLatestLocation, escapeHtml(String(latest.location)), 'trap-latest-location')
        ].join('');

        tableBody.innerHTML = normalised.map(trap => {
            const trapDateTime = formatTrapDateTime(trap.time);
            return `
                <tr>
                    <td>${escapeHtml(String(trap.grade))}</td>
                    <td>${escapeHtml(String(trap.points))}</td>
                    <td>${escapeHtml(String(trap.wire))}</td>
                    <td>${escapeHtml(String(trap.aircraft))}</td>
                    <td>${escapeHtml(String(trap.location))}</td>
                    <td class="trap-date-time-cell">
                        <span class="trap-date">${escapeHtml(trapDateTime.date)}</span>
                        ${trapDateTime.time ? `<span class="trap-time">${escapeHtml(trapDateTime.time)}</span>` : ''}
                    </td>
                </tr>
            `;
        }).join('');
        tableWrap.style.display = 'block';
    } catch (error) {
        console.warn('Could not load carrier trap data:', error);
        status.textContent = i18n.trapLoadError;
    }
}

function themeRgba(name, alpha, fallback) {
    const hex = cssThemeValue(name, fallback).replace('#', '');
    if (!/^[0-9a-fA-F]{6}$/.test(hex)) {
        return fallback;
    }
    const value = parseInt(hex, 16);
    const red = (value >> 16) & 255;
    const green = (value >> 8) & 255;
    const blue = value & 255;
    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

function buildPilotChartTheme() {
    return {
        primary: cssThemeValue('--accent_color', '#4CAF50'),
        secondary: cssThemeValue('--accent_hover_color', '#2196F3'),
        danger: cssThemeValue('--danger_color', '#f44336'),
        warning: cssThemeValue('--warning_color', '#ff9800'),
        muted: cssThemeValue('--muted_text_color', '#cccccc'),
        text: cssThemeValue('--card_text_color', '#ffffff'),
        heading: cssThemeValue('--card_heading_color', '#4CAF50'),
        grid: themeRgba('--border_color', 0.55, 'rgba(85, 107, 47, 0.55)'),
        tooltipBg: cssThemeValue('--surface_dark_color', '#1e1e1e'),
        surface: cssThemeValue('--surface_color', '#2c2c2c'),
        primaryFill: themeRgba('--accent_color', 0.65, 'rgba(76, 175, 80, 0.65)'),
        secondaryFill: themeRgba('--accent_hover_color', 0.65, 'rgba(33, 150, 243, 0.65)'),
        dangerFill: themeRgba('--danger_color', 0.65, 'rgba(244, 67, 54, 0.65)'),
        warningFill: themeRgba('--warning_color', 0.65, 'rgba(255, 152, 0, 0.65)'),
        mutedFill: themeRgba('--muted_text_color', 0.5, 'rgba(158, 158, 158, 0.5)')
    };
}

const pilotChartTheme = buildPilotChartTheme();

function makeAxisTitle(text) {
    return {
        display: true,
        text,
        color: pilotChartTheme.heading,
        font: {
            size: 14,
            weight: 'bold'
        }
    };
}

// Chart configuration using active theme colours
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: pilotChartTheme.muted,
                font: {
                    size: 12
                }
            }
        },
        tooltip: {
            backgroundColor: pilotChartTheme.tooltipBg,
            titleColor: pilotChartTheme.heading,
            bodyColor: pilotChartTheme.muted,
            borderColor: pilotChartTheme.grid,
            borderWidth: 1
        }
    },
    scales: {
        x: {
            ticks: {
                color: pilotChartTheme.muted
            },
            grid: {
                color: pilotChartTheme.grid,
                borderColor: pilotChartTheme.grid
            },
            title: makeAxisTitle(i18n.statistics)
        },
        y: {
            ticks: {
                color: pilotChartTheme.muted
            },
            grid: {
                color: pilotChartTheme.grid,
                borderColor: pilotChartTheme.grid
            },
            title: makeAxisTitle(i18n.count)
        }
    }
};

function createCombatChart(statsData) {
    const ctx = document.getElementById('combatChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (combatChart) {
        combatChart.destroy();
    }
    
    // Only show data that exists
    const labels = [];
    const data = [];
    const backgroundColor = [];
    const borderColor = [];
    
    if (statsData.kills !== undefined) {
        labels.push(i18n.kills);
        data.push(statsData.kills || 0);
        backgroundColor.push(pilotChartTheme.primaryFill);
        borderColor.push(pilotChartTheme.primary);
    }
    
    if (statsData.deaths !== undefined) {
        labels.push(i18n.deaths);
        data.push(statsData.deaths || 0);
        backgroundColor.push(pilotChartTheme.dangerFill);
        borderColor.push(pilotChartTheme.danger);
    }
    
    // Don't create chart if no data
    if (labels.length === 0) {
        const chartWrapper = ctx.parentElement.parentElement;
        if (chartWrapper) {
            chartWrapper.style.display = 'none';
        }
        return;
    }
    
    combatChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: i18n.combatStats,
                data: data,
                backgroundColor: backgroundColor,
                borderColor: borderColor,
                borderWidth: 1
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                ...chartOptions.plugins,
                legend: {
                    display: false
                }
            },
            scales: {
                ...chartOptions.scales,
                x: {
                    ...chartOptions.scales.x,
                    title: {
                        ...makeAxisTitle(i18n.combatMetrics)
                    }
                },
                y: {
                    ...chartOptions.scales.y,
                    title: {
                        ...makeAxisTitle(i18n.numberOfEvents)
                    }
                }
            }
        }
    });
}

function createFlightChart(statsData) {
    const ctx = document.getElementById('flightChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (flightChart) {
        flightChart.destroy();
    }
    
    const takeoffs = statsData.takeoffs || 0;
    const landings = statsData.landings || 0;
    const crashes = statsData.crashes || 0;
    const ejections = statsData.ejections || 0;
    
    // Only include data that exists
    const labels = [];
    const data = [];
    const backgroundColor = [];
    const borderColor = [];
    
    if (statsData.landings !== undefined) {
        labels.push(i18n.successfulLandings);
        data.push(landings);
        backgroundColor.push(pilotChartTheme.primaryFill);
        borderColor.push(pilotChartTheme.primary);
    }
    
    if (statsData.crashes !== undefined) {
        labels.push(i18n.crashes);
        data.push(crashes);
        backgroundColor.push(pilotChartTheme.dangerFill);
        borderColor.push(pilotChartTheme.danger);
    }
    
    if (statsData.ejections !== undefined) {
        labels.push(i18n.ejections);
        data.push(ejections);
        backgroundColor.push(pilotChartTheme.warningFill);
        borderColor.push(pilotChartTheme.warning);
    }
    
    if (statsData.takeoffs !== undefined && takeoffs > (landings + crashes + ejections)) {
        labels.push(i18n.inFlight);
        data.push(Math.max(0, takeoffs - landings - crashes - ejections));
        backgroundColor.push(pilotChartTheme.mutedFill);
        borderColor.push(pilotChartTheme.muted);
    }
    
    // Don't create chart if no data
    if (labels.length === 0) {
        const chartWrapper = ctx.parentElement.parentElement;
        if (chartWrapper) {
            chartWrapper.style.display = 'none';
        }
        return;
    }
    
    flightChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColor,
                borderColor: borderColor,
                borderWidth: 1
            }]
        },
        options: {
            ...chartOptions,
            scales: {} // Remove scales for doughnut chart
        }
    });
}

function createAircraftChart(aircraftData) {
    const ctx = document.getElementById('aircraftChart').getContext('2d');
    
    // Show the wrapper
    document.getElementById('aircraftChartWrapper').style.display = 'block';
    
    // Destroy existing chart if it exists
    if (aircraftChart) {
        aircraftChart.destroy();
    }
    
    const labels = aircraftData.map(a => a.name);
    const data = aircraftData.map(a => a.count);
    
    // Generate colors for each aircraft
    const colors = [
        pilotChartTheme.primaryFill,
        pilotChartTheme.secondaryFill,
        pilotChartTheme.warningFill,
        pilotChartTheme.dangerFill,
        pilotChartTheme.mutedFill
    ];
    
    const borderColors = [
        pilotChartTheme.primary,
        pilotChartTheme.secondary,
        pilotChartTheme.warning,
        pilotChartTheme.danger,
        pilotChartTheme.muted
    ];
    
    aircraftChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: i18n.timesUsed,
                data: data,
                backgroundColor: colors.slice(0, data.length),
                borderColor: borderColors.slice(0, data.length),
                borderWidth: 1
            }]
        },
        options: {
            ...chartOptions,
            indexAxis: 'y', // Horizontal bar chart
            plugins: {
                ...chartOptions.plugins,
                legend: {
                    display: false
                }
            },
            scales: {
                ...chartOptions.scales,
                x: {
                    ...chartOptions.scales.x,
                    beginAtZero: true,
                    ticks: {
                        ...chartOptions.scales.x.ticks,
                        stepSize: 1
                    },
                    title: {
                        ...makeAxisTitle(i18n.timesUsed)
                    }
                },
                y: {
                    ...chartOptions.scales.y,
                    title: {
                        ...makeAxisTitle(i18n.aircraftType)
                    }
                }
            }
        }
    });
}

function createTrapScoresChart(trapScores) {
    const ctx = document.getElementById('trapScoresChart').getContext('2d');
    
    // Show the wrapper
    document.getElementById('trapScoresChartWrapper').style.display = 'block';
    
    // Destroy existing chart if it exists
    if (trapScoresChart) {
        trapScoresChart.destroy();
    }
    
    // Create histogram data for trap scores with carrier-specific grading
    const scoreBuckets = {
        'OK (4.0)': 0,
        'Fair (3.0-3.9)': 0,
        'No Grade (2.0-2.9)': 0,
        'Cut (1.0-1.9)': 0,
        'Wave Off (0-0.9)': 0
    };
    
    trapScores.forEach(score => {
        if (score >= 4) scoreBuckets['OK (4.0)']++;
        else if (score >= 3) scoreBuckets['Fair (3.0-3.9)']++;
        else if (score >= 2) scoreBuckets['No Grade (2.0-2.9)']++;
        else if (score >= 1) scoreBuckets['Cut (1.0-1.9)']++;
        else scoreBuckets['Wave Off (0-0.9)']++;
    });
    
    const labels = Object.keys(scoreBuckets);
    const data = Object.values(scoreBuckets);
    
    // Generate colors matching carrier grading standards
    const colors = [
        pilotChartTheme.primaryFill,
        pilotChartTheme.secondaryFill,
        pilotChartTheme.mutedFill,
        pilotChartTheme.warningFill,
        pilotChartTheme.dangerFill
    ];
    
    const borderColors = [
        pilotChartTheme.primary,
        pilotChartTheme.secondary,
        pilotChartTheme.muted,
        pilotChartTheme.warning,
        pilotChartTheme.danger
    ];
    
    trapScoresChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Traps',
                data: data,
                backgroundColor: colors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                ...chartOptions.plugins,
                legend: {
                    display: false
                },
                tooltip: {
                    ...chartOptions.plugins.tooltip,
                    callbacks: {
                        afterLabel: function(context) {
                            const total = trapScores.length;
                            const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                            return `${percentage}% of total traps`;
                        }
                    }
                }
            },
            scales: {
                ...chartOptions.scales,
                x: {
                    ...chartOptions.scales.x,
                    title: {
                        ...makeAxisTitle('Landing Grade')
                    }
                },
                y: {
                    ...chartOptions.scales.y,
                    beginAtZero: true,
                    ticks: {
                        ...chartOptions.scales.y.ticks,
                        stepSize: 1
                    },
                    title: {
                        ...makeAxisTitle('Number of Carrier Traps')
                    }
                }
            }
        }
    });
}

// Allow Enter key to trigger search
document.getElementById('playerSearchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchForPlayers();
    }
});

// Check for search parameter in URL and auto-search
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');
    
    if (searchParam) {
        // Set the search input value
        const searchInput = document.getElementById('playerSearchInput');
        if (searchInput) {
            searchInput.value = searchParam;
            // Trigger the search automatically
            searchForPlayers();
        }
    }
});
