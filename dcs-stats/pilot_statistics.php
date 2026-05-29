<?php 
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php'; 
?>
<?php require_once __DIR__ . '/site_features.php'; ?>
<?php require_once __DIR__ . '/table-responsive.php'; ?>
<?php require_once __DIR__ . '/language.php'; ?>
<script src="<?php echo htmlspecialchars(assetUrl('js/vendor/chart.umd.min.js')); ?>"></script>
<?php include 'nav.php'; ?>

<main>
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars(dcs_t('pilot.title')); ?></h1>
        <p class="dashboard-subtitle"><?php echo htmlspecialchars(dcs_t('pilot.subtitle')); ?></p>
    </div>
    
    <?php if (isFeatureEnabled('pilot_search')): ?>
    <div class="search-container">
        <input type="text" id="playerSearchInput" placeholder="<?php echo htmlspecialchars(dcs_t('pilot.search_placeholder')); ?>" />
        <button onclick="searchForPlayers()"><?php echo htmlspecialchars(dcs_t('pilot.search_button')); ?></button>
    </div>
    <?php else: ?>
    <div class="alert" style="text-align: center; padding: 20px;">
        <p><?php echo htmlspecialchars(dcs_t('pilot.search_disabled')); ?></p>
    </div>
    <?php endif; ?>
    
    <div id="multiple-results" style="display: none;">
        <h3 style="text-align: center; color: #ccc;"><?php echo htmlspecialchars(dcs_t('pilot.multiple_found')); ?></h3>
        <div id="results-list" class="results-list"></div>
    </div>
    
    <div id="search-results" style="display: none;">
        <div id="pilot-card" class="pilot-card">
            <h3 id="pilot-name"></h3>
            <div class="pilot-stats">
                <div class="stat-group" id="combat-stats-group">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.combat_statistics')); ?></h4>
                    <div class="stats-grid" id="combat-stats-grid">
                        <!-- Combat stats will be dynamically added here -->
                    </div>
                </div>
                
                <div class="stat-group" id="secondary-stats-group" style="display: none;">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.additional_information')); ?></h4>
                    <div class="stats-grid" id="secondary-stats-grid">
                        <!-- Secondary stats will be dynamically added here -->
                    </div>
                </div>
                
                <div class="stat-group" id="session-stats-group" style="display: none;">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.last_session')); ?></h4>
                    <div class="stats-grid" id="session-stats-grid">
                        <!-- Session stats will be dynamically added here -->
                    </div>
                </div>
            </div>
            
            <div class="charts-container">
                <?php if (isFeatureEnabled('pilot_combat_stats')): ?>
                <div class="chart-wrapper" title="<?php echo htmlspecialchars(dcs_t('pilot.combat_performance_title')); ?>">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.combat_performance')); ?> <span class="chart-info">ⓘ</span></h4>
                    <canvas id="combatChart"></canvas>
                </div>
                <?php endif; ?>
                <?php if (isFeatureEnabled('pilot_flight_stats')): ?>
                <div class="chart-wrapper" title="<?php echo htmlspecialchars(dcs_t('pilot.flight_statistics_title')); ?>">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.flight_statistics')); ?> <span class="chart-info">ⓘ</span></h4>
                    <canvas id="flightChart"></canvas>
                </div>
                <?php endif; ?>
                <?php if (isFeatureEnabled('pilot_aircraft_chart')): ?>
                <div class="chart-wrapper" id="aircraftChartWrapper" style="display: none;" title="<?php echo htmlspecialchars(dcs_t('pilot.aircraft_usage_title')); ?>">
                    <h4><?php echo htmlspecialchars(dcs_t('pilot.aircraft_usage')); ?> <span class="chart-info">ⓘ</span></h4>
                    <canvas id="aircraftChart"></canvas>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isFeatureEnabled('pilot_carrier_traps')): ?>
        <div class="pilot-card carrier-traps-card" id="carrier-traps-group" style="display: none;">
            <h3><?php echo htmlspecialchars(dcs_t('pilot.carrier_landings')); ?></h3>
            <p class="carrier-traps-status" id="carrier-traps-status"></p>
            <div class="stats-grid" id="carrier-traps-summary"></div>
            <div class="carrier-traps-table-wrap" id="carrier-traps-table-wrap" style="display: none;">
                <table class="carrier-traps-table">
                    <thead>
                        <tr>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_grade')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_points')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_wire')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_aircraft')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_location')); ?></th>
                            <th><?php echo htmlspecialchars(dcs_t('pilot.trap_time')); ?></th>
                        </tr>
                    </thead>
                    <tbody id="carrier-traps-table-body"></tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div id="no-results" style="display: none; text-align: center; color: #ccc; margin-top: 30px;">
        <p id="no-results-message"><?php echo htmlspecialchars(dcs_t('pilot.no_pilot_found')); ?></p>
    </div>
    
    <div id="loading" style="display: none; text-align: center; color: #ccc; margin-top: 30px;">
        <p><?php echo htmlspecialchars(dcs_t('pilot.searching')); ?></p>
    </div>
</main>

<script>
const i18n = <?php echo json_encode([
    'enterName' => dcs_t('pilot.enter_name'),
    'noMatches' => dcs_t('pilot.no_matches'),
    'checkSpelling' => dcs_t('pilot.check_spelling'),
    'usePartial' => dcs_t('pilot.use_partial'),
    'searchStart' => dcs_t('pilot.search_start'),
    'searchError' => dcs_t('pilot.search_error'),
    'loadError' => dcs_t('pilot.load_error'),
    'consoleDetails' => dcs_t('pilot.console_details'),
    'noPilotFound' => dcs_t('pilot.no_pilot_found'),
    'noStatsEnabled' => dcs_t('pilot.no_stats_enabled'),
    'contactAdmin' => dcs_t('pilot.contact_admin'),
    'kills' => dcs_t('pilot.kills'),
    'deaths' => dcs_t('pilot.deaths'),
    'kdRatio' => dcs_t('pilot.kd_ratio'),
    'takeoffs' => dcs_t('pilot.takeoffs'),
    'landings' => dcs_t('pilot.landings'),
    'crashes' => dcs_t('pilot.crashes'),
    'ejections' => dcs_t('pilot.ejections'),
    'credits' => dcs_t('pilot.credits'),
    'mostUsedAircraft' => dcs_t('pilot.most_used_aircraft'),
    'squadron' => dcs_t('pilot.squadron'),
    'none' => dcs_t('pilot.none'),
    'sessionKills' => dcs_t('pilot.session_kills'),
    'sessionDeaths' => dcs_t('pilot.session_deaths'),
    'statistics' => dcs_t('pilot.statistics'),
    'count' => dcs_t('pilot.count'),
    'combatMetrics' => dcs_t('pilot.combat_metrics'),
    'numberOfEvents' => dcs_t('pilot.number_of_events'),
    'combatStats' => dcs_t('pilot.combat_stats'),
    'successfulLandings' => dcs_t('pilot.successful_landings'),
    'inFlight' => dcs_t('pilot.in_flight'),
    'timesUsed' => dcs_t('pilot.times_used'),
    'aircraftType' => dcs_t('pilot.aircraft_type'),
    'carrierLandings' => dcs_t('pilot.carrier_landings'),
    'loadingTraps' => dcs_t('pilot.loading_traps'),
    'noTrapData' => dcs_t('pilot.no_trap_data'),
    'trapLoadError' => dcs_t('pilot.trap_load_error'),
    'trapLatestGrade' => dcs_t('pilot.trap_latest_grade'),
    'trapLatestPoints' => dcs_t('pilot.trap_latest_points'),
    'trapLatestWire' => dcs_t('pilot.trap_latest_wire'),
    'trapLatestAircraft' => dcs_t('pilot.trap_latest_aircraft'),
    'trapLatestCase' => dcs_t('pilot.trap_latest_case'),
    'trapLatestLocation' => dcs_t('pilot.trap_latest_location')
], JSON_UNESCAPED_UNICODE); ?>;

// Feature flags from PHP
const siteFeatures = {
    credits: <?php echo json_encode(isFeatureEnabled('credits_enabled')); ?>,
    squadrons: <?php echo json_encode(isFeatureEnabled('squadrons_enabled')); ?>,
    leaderboard_kills: <?php echo json_encode(isFeatureEnabled('leaderboard_kills')); ?>,
    leaderboard_deaths: <?php echo json_encode(isFeatureEnabled('leaderboard_deaths')); ?>,
    leaderboard_kd_ratio: <?php echo json_encode(isFeatureEnabled('leaderboard_kd_ratio')); ?>,
    leaderboard_aircraft: <?php echo json_encode(isFeatureEnabled('leaderboard_aircraft')); ?>,
    pilot_combat_stats: <?php echo json_encode(isFeatureEnabled('pilot_combat_stats')); ?>,
    pilot_flight_stats: <?php echo json_encode(isFeatureEnabled('pilot_flight_stats')); ?>,
    pilot_session_stats: <?php echo json_encode(isFeatureEnabled('pilot_session_stats')); ?>,
    pilot_aircraft_chart: <?php echo json_encode(isFeatureEnabled('pilot_aircraft_chart')); ?>,
    pilot_carrier_traps: <?php echo json_encode(isFeatureEnabled('pilot_carrier_traps')); ?>
};

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

        tableBody.innerHTML = normalised.map(trap => `
            <tr>
                <td>${escapeHtml(String(trap.grade))}</td>
                <td>${escapeHtml(String(trap.points))}</td>
                <td>${escapeHtml(String(trap.wire))}</td>
                <td>${escapeHtml(String(trap.aircraft))}</td>
                <td>${escapeHtml(String(trap.location))}</td>
                <td>${escapeHtml(formatTrapTime(trap.time))}</td>
            </tr>
        `).join('');
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
</script>

<style>
/* Main content uses responsive styling from styles.css */

/* Pilot card styling moved to unified styles.css */

/* Search styling moved to unified styles.css */

/* Results styling moved to unified styles.css */

/* Squadron styling moved to unified styles.css */

.charts-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    margin: 40px auto 0;
    max-width: 1000px;
}

@media (max-width: 768px) {
    .charts-container {
        grid-template-columns: 1fr;
    }
}

.chart-wrapper {
    background-color: #1e1e1e;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

.chart-wrapper h4 {
    color: #4CAF50;
    margin-bottom: 20px;
    text-align: center;
    font-size: 1.2rem;
}

.chart-wrapper canvas {
    max-height: 250px;
}

.chart-info {
    display: inline-block;
    width: 16px;
    height: 16px;
    line-height: 16px;
    text-align: center;
    background-color: #444;
    color: #ccc;
    border-radius: 50%;
    font-size: 12px;
    margin-left: 5px;
    cursor: help;
    transition: all 0.3s ease;
}

.chart-info:hover {
    background-color: #4CAF50;
    color: white;
    transform: scale(1.1);
}

.chart-wrapper {
    position: relative;
}

.chart-wrapper:hover {
    box-shadow: 0 0 15px rgba(76, 175, 80, 0.3);
}

.chart-wrapper[title] {
    cursor: help;
}

.no-stats-message {
    text-align: center;
    padding: 40px 20px;
    color: #888;
    background-color: #1a1a1a;
    border-radius: 8px;
    margin: 20px 0;
}

.no-stats-message p {
    margin: 10px 0;
    font-size: 1rem;
}

.no-stats-message p:first-child {
    font-size: 1.2rem;
    color: #ccc;
}

.carrier-traps-card {
    margin-top: 24px;
}

.carrier-traps-status {
    color: var(--card_muted_text_color, #b0b0b0);
    margin: 0 0 16px;
}

.carrier-traps-table-wrap {
    margin-top: 18px;
    overflow-x: auto;
}

.carrier-traps-table {
    border-collapse: collapse;
    width: 100%;
}

.carrier-traps-table th,
.carrier-traps-table td {
    border-bottom: 1px solid color-mix(in srgb, var(--border_color, #444) 60%, transparent);
    color: var(--card_text_color, #e0e0e0);
    padding: 10px 12px;
    text-align: left;
}

.carrier-traps-table th {
    color: var(--card_heading_color, #4CAF50);
    font-size: 0.85rem;
    text-transform: uppercase;
}

/* Enhanced tooltip styling */
.chart-wrapper:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: #fff;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    white-space: nowrap;
    z-index: 1000;
    pointer-events: none;
    opacity: 0;
    animation: fadeIn 0.3s forwards;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.chart-wrapper:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 8px solid transparent;
    border-top-color: #333;
    margin-bottom: 2px;
    opacity: 0;
    animation: fadeIn 0.3s forwards;
}

@keyframes fadeIn {
    to {
        opacity: 1;
    }
}

/* Mobile Responsive Styles */
@media screen and (max-width: 768px) {
    /* Search container mobile optimization */
    .search-container {
        padding: 0 15px;
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    #playerSearchInput {
        width: 100%;
        padding: 12px 15px;
        font-size: 16px; /* Prevents zoom on iOS */
        border-radius: 25px;
    }
    
    .search-container button {
        width: 100%;
        padding: 12px 20px;
        font-size: 1rem;
        border-radius: 25px;
        min-height: 44px;
    }
    
    /* Results list mobile optimization */
    .results-list {
        padding: 0 10px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .result-item {
        padding: 15px;
        margin: 8px 0;
        font-size: 1rem;
    }
    
    /* Pilot card mobile optimization */
    .pilot-card {
        padding: 15px;
        margin: 15px;
    }
    
    .pilot-card h3 {
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
    
    /* Stats grid mobile layout */
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 5px;
    }
    
    .stat-label {
        font-weight: 600;
        color: #4CAF50;
    }
    
    .stat-value {
        font-weight: bold;
    }
    
    /* Charts mobile optimization */
    .charts-container {
        grid-template-columns: 1fr !important;
        gap: 20px;
        margin: 20px 0;
    }
    
    .chart-wrapper {
        padding: 15px;
    }
    
    .chart-wrapper h4 {
        font-size: 1rem;
        margin-bottom: 15px;
    }
    
    .chart-wrapper canvas {
        max-height: 200px !important;
    }
    
    /* Stat groups */
    .stat-group h4 {
        font-size: 1.1rem;
        margin: 20px 0 10px 0;
        color: #4CAF50;
    }
    
    /* Dashboard header mobile */
    .dashboard-header h1 {
        font-size: 1.8rem;
    }
    
    .dashboard-subtitle {
        font-size: 0.9rem;
        padding: 0 10px;
    }
}

/* Very small devices */
@media screen and (max-width: 480px) {
    .pilot-card {
        margin: 10px;
        padding: 10px;
    }
    
    .pilot-card h3 {
        font-size: 1.2rem;
    }
    
    .stat-item {
        font-size: 0.9rem;
    }
    
    .chart-wrapper canvas {
        max-height: 180px !important;
    }
}

/* Touch-friendly hover states */
@media (hover: none) and (pointer: coarse) {
    .result-item:hover {
        background-color: transparent;
    }
    
    .result-item:active {
        background-color: rgba(76, 175, 80, 0.3);
    }
}
</style>

<?php tableResponsiveStyles(); ?>

<?php include 'footer.php'; ?>
