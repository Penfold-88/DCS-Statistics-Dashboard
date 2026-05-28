<?php 
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config_path.php';
require_once __DIR__ . '/site_features.php';
require_once __DIR__ . '/chart_theme.php';
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/install_checkin.php';
require_once __DIR__ . '/site-config/update_channel.php';
require_once __DIR__ . '/site-config/version_tracker.php';

$apiConfigExists = file_exists(__DIR__ . '/api_config.json') ||
                   file_exists(__DIR__ . '/site-config/data/api_config.json');
$isConfigured = $apiConfigExists &&
                file_exists(__DIR__ . '/site-config/data/users.json');

if (!$isConfigured) {
    header('Location: ' . url('site-config/install.php'));
    exit;
}

include 'header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include 'nav.php'; ?>

<?php
runInstallCheckinIfDue(getCurrentVersionInfo(), getUpdateChannelConfig());

$showAttendanceCards = isFeatureEnabled('home_attendance_cards') && (
                       isFeatureEnabled('home_api_players_24h') ||
                       isFeatureEnabled('home_api_players_7d') ||
                       isFeatureEnabled('home_api_players_30d') ||
                       isFeatureEnabled('home_api_current_players')
);
$showApiInsights = isFeatureEnabled('home_api_insights');
$showTopApiLists = $showApiInsights && (
                   isFeatureEnabled('home_top_theatres') ||
                   isFeatureEnabled('home_top_missions') ||
                   isFeatureEnabled('home_top_modules')
);
$showTopPilotsChart = isFeatureEnabled('home_top_pilots');
$showTopSquadronsChart = isFeatureEnabled('squadrons_enabled') && isFeatureEnabled('home_top_pilots');
$showCoreServerStats = isFeatureEnabled('home_server_stats') ||
                       isFeatureEnabled('home_mission_stats') ||
                       isFeatureEnabled('home_player_activity');
$homepageChartTheme = loadChartTheme();
?>
<main>
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars(dcs_t('home.title')); ?></h1>
        <p class="dashboard-subtitle"><?php echo htmlspecialchars(dcs_t('home.subtitle')); ?></p>
    </div>
    
    <?php if (isFeatureEnabled('home_server_stats')): ?>
    <div class="stats-cards">
        <div class="stat-card" id="totalPlayersCard">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo htmlspecialchars(dcs_t('home.total_players')); ?></h3>
                <p class="stat-number" id="totalPlayers">-</p>
            </div>
        </div>
        
        <div class="stat-card" id="totalPlaytimeCard">
            <div class="stat-icon">✈️</div>
            <div class="stat-content">
                <h3><?php echo htmlspecialchars(dcs_t('home.total_playtime')); ?></h3>
                <p class="stat-number" id="totalPlaytime">-</p>
            </div>
        </div>
        
        <div class="stat-card" id="avgPlaytimeCard">
            <div class="stat-icon">🕐</div>
            <div class="stat-content">
                <h3><?php echo htmlspecialchars(dcs_t('home.average_playtime')); ?></h3>
                <p class="stat-number" id="avgPlaytime">-</p>
            </div>
        </div>
        
        <div class="stat-card" id="totalSortiesCard">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3><?php echo htmlspecialchars(dcs_t('home.total_sorties')); ?></h3>
                <p class="stat-number" id="totalSorties">-</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($showAttendanceCards): ?>
    <div class="api-attendance" id="apiAttendance" style="display: none;">
        <div class="insight-cards">
            <?php if (isFeatureEnabled('home_api_players_24h')): ?>
            <div class="insight-card">
                <div class="insight-icon">👥</div>
                <div class="insight-content">
                    <span><?php echo htmlspecialchars(dcs_t('home.players_24h')); ?></span>
                    <strong id="players24h">-</strong>
                </div>
            </div>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_api_players_7d')): ?>
            <div class="insight-card">
                <div class="insight-icon">👥</div>
                <div class="insight-content">
                    <span><?php echo htmlspecialchars(dcs_t('home.players_7d')); ?></span>
                    <strong id="players7d">-</strong>
                </div>
            </div>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_api_players_30d')): ?>
            <div class="insight-card">
                <div class="insight-icon">👥</div>
                <div class="insight-content">
                    <span><?php echo htmlspecialchars(dcs_t('home.players_30d')); ?></span>
                    <strong id="players30d">-</strong>
                </div>
            </div>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_api_current_players')): ?>
            <div class="insight-card">
                <div class="insight-icon">👥</div>
                <div class="insight-content">
                    <span><?php echo htmlspecialchars(dcs_t('home.current_players')); ?></span>
                    <strong id="currentPlayers">-</strong>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="charts-dashboard">
        <?php if (isFeatureEnabled('home_top_pilots')): ?>
        <div class="chart-container" title="<?php echo htmlspecialchars(dcs_t('home.chart_top_pilots_title')); ?>">
            <div class="chart-card-header">
                <h2><?php echo htmlspecialchars(dcs_t('home.chart_top_pilots')); ?> <span class="chart-info">ⓘ</span></h2>
                <label class="chart-metric-control" for="topPilotsMetric">
                    <span class="chart-metric-select server-scope-control">
                        <select id="topPilotsMetric">
                            <option value="kills"><?php echo htmlspecialchars(dcs_t('home.kills')); ?></option>
                            <option value="kdr"><?php echo htmlspecialchars(dcs_t('home.kill_death_ratio')); ?></option>
                            <option value="kdr_pvp"><?php echo htmlspecialchars(dcs_t('home.pvp_kill_death_ratio')); ?></option>
                        </select>
                    </span>
                </label>
            </div>
            <canvas id="topPilotsChart"></canvas>
            <p class="no-data-message" id="topPilotsNoData" style="display: none;"><?php echo htmlspecialchars(dcs_t('home.no_mission_data')); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('home_mission_stats')): ?>
        <div class="chart-container" title="<?php echo htmlspecialchars(dcs_t('home.chart_combat_stats_title')); ?>">
            <h2><?php echo htmlspecialchars(dcs_t('home.chart_combat_stats')); ?> <span class="chart-info">ⓘ</span></h2>
            <canvas id="combatStatsChart"></canvas>
        </div>
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('squadrons_enabled') && isFeatureEnabled('home_top_pilots')): ?>
        <div class="chart-container" title="<?php echo htmlspecialchars(dcs_t('home.chart_top_squadrons_title')); ?>">
            <h2><?php echo htmlspecialchars(dcs_t('home.chart_top_squadrons')); ?> <span class="chart-info">ⓘ</span></h2>
            <canvas id="topSquadronsChart"></canvas>
            <p class="no-data-message" id="squadronsNoData" style="display: none;"><?php echo htmlspecialchars(dcs_t('home.no_squadron_data')); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('home_player_activity')): ?>
        <div class="chart-container full-width" title="<?php echo htmlspecialchars(dcs_t('home.chart_activity_title')); ?>">
            <h2><?php echo htmlspecialchars(dcs_t('home.chart_activity')); ?> <span class="chart-info">ⓘ</span></h2>
            <canvas id="playerActivityChart"></canvas>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($showTopApiLists): ?>
    <div class="api-insights" id="apiInsights" style="display: none;">
        <?php if ($showTopApiLists): ?>
        <div class="insight-grid">
            <?php if (isFeatureEnabled('home_top_theatres')): ?>
            <section class="insight-panel">
                <h3><?php echo htmlspecialchars(dcs_t('home.top_theatres')); ?></h3>
                <div id="topTheatresList" class="rank-list"></div>
            </section>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_top_missions')): ?>
            <section class="insight-panel">
                <h3><?php echo htmlspecialchars(dcs_t('home.top_missions')); ?></h3>
                <div id="topMissionsList" class="rank-list"></div>
            </section>
            <?php endif; ?>
            <?php if (isFeatureEnabled('home_top_modules')): ?>
            <section class="insight-panel">
                <h3><?php echo htmlspecialchars(dcs_t('home.top_modules')); ?></h3>
                <div id="topModulesList" class="rank-list"></div>
            </section>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div id="loading-overlay" class="loading-overlay">
        <div class="loader"></div>
        <p><?php echo htmlspecialchars(dcs_t('home.loading_stats')); ?></p>
    </div>
</main>

<script>
const i18n = <?= json_encode([
    'unknown' => dcs_t('home.unknown'),
    'noData' => dcs_t('home.no_data'),
    'kills' => dcs_t('home.kills'),
    'deaths' => dcs_t('home.deaths'),
    'kdRatio' => dcs_t('home.kill_death_ratio'),
    'pvpKdRatio' => dcs_t('home.pvp_kill_death_ratio'),
    'pilotNames' => dcs_t('home.pilot_names'),
    'numberOfKills' => dcs_t('home.number_of_kills'),
    'numberOfKDRatio' => dcs_t('home.number_of_kd_ratio'),
    'numberOfPvpKDRatio' => dcs_t('home.number_of_pvp_kd_ratio'),
    'combatResults' => dcs_t('home.combat_results'),
    'count' => dcs_t('home.count'),
    'squadrons' => dcs_t('home.squadrons'),
    'performanceScore' => dcs_t('home.performance_score'),
    'hours' => dcs_t('home.hours'),
    'pilots' => dcs_t('home.pilots'),
    'players' => dcs_t('home.players'),
    'numberOfPlayers' => dcs_t('home.number_of_players'),
    'totalKills' => dcs_t('home.total_kills'),
    'totalDeaths' => dcs_t('home.total_deaths'),
    'totalCredits' => dcs_t('home.total_credits'),
    'squadronCredits' => dcs_t('home.squadron_credits'),
    'squadronNames' => dcs_t('home.squadron_names'),
    'dailyPlayers' => dcs_t('home.daily_players'),
    'date' => dcs_t('home.date')
], JSON_UNESCAPED_UNICODE) ?>;

// Chart instances
let topPilotsChart = null;
let combatStatsChart = null;
let playerActivityChart = null;
let topSquadronsChart = null;
let latestTopPilots = [];

const homepageChartTheme = <?= json_encode($homepageChartTheme) ?>;
const homepageDataNeeds = <?= json_encode([
    'loadServerStats' => $showCoreServerStats,
    'loadAttendance' => $showAttendanceCards || $showTopApiLists,
    'loadTopPilots' => $showTopPilotsChart,
    'loadSquadrons' => $showTopSquadronsChart
]) ?>;

function chartThemeColor(key, fallbackKey, fallbackColor) {
    return homepageChartTheme[key] || homepageChartTheme[fallbackKey] || fallbackColor;
}

function hexToRgba(hex, alpha = 1) {
    const cleanHex = String(hex || '#ffffff').replace('#', '');
    const value = parseInt(cleanHex.length === 3 ? cleanHex.split('').map(c => c + c).join('') : cleanHex, 16);
    const red = (value >> 16) & 255;
    const green = (value >> 8) & 255;
    const blue = value & 255;
    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

const chartColors = {
    topPilots: {
        main: chartThemeColor('home_top_pilots_color', 'home_chart_primary_color', '#4CAF50'),
        grid: chartThemeColor('home_top_pilots_grid_color', 'home_chart_grid_color', '#2d4a2f'),
        text: chartThemeColor('home_top_pilots_text_color', 'home_chart_text_color', '#e0e0e0')
    },
    combat: {
        kills: chartThemeColor('home_combat_kills_color', 'home_chart_secondary_color', '#2196F3'),
        deaths: chartThemeColor('home_combat_deaths_color', 'home_chart_danger_color', '#f44336'),
        text: chartThemeColor('home_combat_text_color', 'home_chart_text_color', '#e0e0e0')
    },
    squadrons: {
        main: chartThemeColor('home_squadrons_color', 'home_chart_warning_color', '#ffc107'),
        grid: chartThemeColor('home_squadrons_grid_color', 'home_chart_grid_color', '#2d4a2f'),
        text: chartThemeColor('home_squadrons_text_color', 'home_chart_text_color', '#e0e0e0')
    },
    activity: {
        main: chartThemeColor('home_activity_color', 'home_chart_primary_color', '#4CAF50'),
        grid: chartThemeColor('home_activity_grid_color', 'home_chart_grid_color', '#2d4a2f'),
        text: chartThemeColor('home_activity_text_color', 'home_chart_text_color', '#e0e0e0')
    }
};

const gradientColors = {
    topPilots: [hexToRgba(chartColors.topPilots.main, 1), hexToRgba(chartColors.topPilots.main, 0.2)],
    combatKills: [hexToRgba(chartColors.combat.kills, 1), hexToRgba(chartColors.combat.kills, 0.2)],
    combatDeaths: [hexToRgba(chartColors.combat.deaths, 1), hexToRgba(chartColors.combat.deaths, 0.2)],
    squadrons: [hexToRgba(chartColors.squadrons.main, 1), hexToRgba(chartColors.squadrons.main, 0.2)],
    activity: [hexToRgba(chartColors.activity.main, 1), hexToRgba(chartColors.activity.main, 0.2)]
};

// Load server statistics
async function loadServerStats() {
    try {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
        }

        // Use the client-side API
        const data = await window.dcsAPI.getServerStats(homepageDataNeeds);
        
        if (data.error) {
            document.getElementById('loading-overlay').style.display = 'none';
            return;
        }
        
        // Update stat cards with animation (if enabled)
        <?php if (isFeatureEnabled('home_server_stats')): ?>
        animateNumber('totalPlayers', data.totalPlayers);
        animateNumber('totalPlaytime', data.totalPlaytime);
        animateNumber('avgPlaytime', data.avgPlaytime / 60);
        animateNumber('totalSorties', data.totalSorties);
        
        // Calculate K/D ratio
        const kdRatio = data.totalDeaths > 0 ? (data.totalKills / data.totalDeaths).toFixed(2) : data.totalKills;
        //document.getElementById('kdRatio').textContent = kdRatio;
        <?php endif; ?>
        
        // Create charts with empty data handling
        <?php if (isFeatureEnabled('home_top_pilots')): ?>
        await loadTopPilotsChart(document.getElementById('topPilotsMetric')?.value || 'kills', data.top5Pilots || []);
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('home_mission_stats')): ?>
        createCombatStatsChart(data.totalKills || 0, data.totalDeaths || 0);
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('squadrons_enabled') && isFeatureEnabled('home_top_pilots')): ?>
        if (data.top3Squadrons && data.top3Squadrons.length > 0) {
            createTopSquadronsChart(data.top3Squadrons);
            document.getElementById('squadronsNoData').style.display = 'none';
        } else {
            document.getElementById('topSquadronsChart').style.display = 'none';
            document.getElementById('squadronsNoData').style.display = 'block';
        }
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('home_player_activity')): ?>
        createPlayerActivityChart(data.activityLastWeek || []);
        <?php endif; ?>

        <?php if ($showAttendanceCards || $showTopApiLists): ?>
        renderApiInsights(data.attendance || {});
        <?php endif; ?>
        
        // Hide loading overlay
        document.getElementById('loading-overlay').style.display = 'none';
        
        // Add pop animations to cards
        <?php if (isFeatureEnabled('home_server_stats')): ?>
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('pop-in');
            }, index * 100);
        });
        <?php endif; ?>
        
    } catch (error) {
        console.error('Error fetching server stats:', error);
        document.getElementById('loading-overlay').style.display = 'none';
    }
}

function renderApiInsights(attendance) {
    const panel = document.getElementById('apiInsights');
    const attendancePanel = document.getElementById('apiAttendance');
    if (!attendance || Object.keys(attendance).length === 0) {
        if (panel) panel.style.display = 'none';
        if (attendancePanel) attendancePanel.style.display = 'none';
        return;
    }

    if (panel) panel.style.display = 'block';
    if (attendancePanel) attendancePanel.style.display = 'block';
    setText('players24h', attendance.unique_players_24h ?? '-');
    setText('players7d', attendance.unique_players_7d ?? '-');
    setText('players30d', attendance.unique_players_30d ?? '-');
    setText('currentPlayers', attendance.current_players ?? '-');

    renderRankList('topTheatresList', attendance.top_theatres || [], item => ({
        title: item.theatre || i18n.unknown,
        value: `${Number(item.playtime_hours || 0).toLocaleString()} ${i18n.hours}`
    }));

    renderRankList('topMissionsList', attendance.top_missions || [], item => ({
        title: item.mission_name || i18n.unknown,
        value: `${Number(item.playtime_hours || 0).toLocaleString()} ${i18n.hours}`
    }));

    renderRankList('topModulesList', attendance.top_modules || [], item => ({
        title: item.module || i18n.unknown,
        value: `${Number(item.playtime_hours || 0).toLocaleString()} ${i18n.hours} | ${Number(item.unique_players || 0).toLocaleString()} ${i18n.pilots}`
    }));
}

function setText(id, value) {
    const element = document.getElementById(id);
    if (!element) return;
    element.textContent = Number.isFinite(Number(value)) ? Number(value).toLocaleString() : value;
}

function renderRankList(id, items, mapItem) {
    const container = document.getElementById(id);
    if (!container) return;

    const rows = items.slice(0, 5).map((item, index) => {
        const mapped = mapItem(item);
        return `
            <div class="rank-row">
                <span class="rank-number">${index + 1}</span>
                <strong>${escapeHtml(mapped.title)}</strong>
                <em>${escapeHtml(mapped.value)}</em>
            </div>
        `;
    });

    container.innerHTML = rows.length ? rows.join('') : `<p class="no-data-message">${escapeHtml(i18n.noData)}</p>`;
}

// Animate numbers counting up
function animateNumber(elementId, targetNumber) {
    const element = document.getElementById(elementId);
    const duration = 1500;
    const start = 0;
    const increment = targetNumber / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= targetNumber) {
            current = targetNumber;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

// Create gradient for charts
function createGradient(ctx, colors) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, colors[0]);
    gradient.addColorStop(1, colors[1]);
    return gradient;
}

// Top 5 pilots chart
const topPilotsMetrics = {
    kills: {
        label: i18n.kills,
        axis: i18n.numberOfKills,
        value: pilot => Number(pilot.kills || 0)
    },
    kdr: {
        label: i18n.kdRatio,
        axis: i18n.numberOfKDRatio,
        value: pilot => Number(pilot.kdr ?? pilot.kd_ratio ?? 0)
    },
    kdr_pvp: {
        label: i18n.pvpKdRatio,
        axis: i18n.numberOfPvpKDRatio,
        value: pilot => Number(pilot.kdr_pvp || 0)
    }
};

async function loadTopPilotsChart(metricName = 'kills', fallbackPilots = []) {
    const canvas = document.getElementById('topPilotsChart');
    const noData = document.getElementById('topPilotsNoData');
    if (!canvas || !noData) return;

    try {
        const metric = topPilotsMetrics[metricName] ? metricName : 'kills';
        let pilots = metric === 'kills' && fallbackPilots.length ? fallbackPilots : [];
        if (!pilots.length && window.dcsAPI?.getTopPilots) {
            pilots = await window.dcsAPI.getTopPilots(metric);
        }

        latestTopPilots = Array.isArray(pilots) ? pilots : [];
        if (latestTopPilots.length > 0) {
            createTopPilotsChart(latestTopPilots, metric);
            canvas.style.display = 'block';
            noData.style.display = 'none';
        } else {
            canvas.style.display = 'none';
            noData.style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading top pilots chart:', error);
        canvas.style.display = 'none';
        noData.style.display = 'block';
    }
}

function createTopPilotsChart(pilots, metricName = 'kills') {
    const ctx = document.getElementById('topPilotsChart').getContext('2d');
    const metric = topPilotsMetrics[metricName] || topPilotsMetrics.kills;
    
    if (topPilotsChart) {
        topPilotsChart.destroy();
    }
    
    const gradient = createGradient(ctx, gradientColors.topPilots);
    
    topPilotsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: pilots.map(p => p.nick),
            datasets: [{
                label: metric.label,
                data: pilots.map(metric.value),
                backgroundColor: gradient,
                borderColor: hexToRgba(chartColors.topPilots.main, 1),
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: chartColors.topPilots.text,
                    bodyColor: chartColors.topPilots.text,
                    borderColor: hexToRgba(chartColors.topPilots.main, 1),
                    borderWidth: 1,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `${metric.label}: ${Number(context.parsed.y || 0).toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: chartColors.topPilots.text,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    title: {
                        display: true,
                        text: i18n.pilotNames,
                        color: hexToRgba(chartColors.topPilots.main, 1),
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: hexToRgba(chartColors.topPilots.grid, 0.45),
                        borderDash: [5, 5]
                    },
                    ticks: {
                        color: chartColors.topPilots.text,
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: metric.axis,
                        color: hexToRgba(chartColors.topPilots.main, 1),
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutBounce'
            }
        }
    });
}

// Combat stats chart
function createCombatStatsChart(kills, deaths) {
    const ctx = document.getElementById('combatStatsChart').getContext('2d');
    
    if (combatStatsChart) {
        combatStatsChart.destroy();
    }
    
    const killGradient = createGradient(ctx, gradientColors.combatKills);
    const deathGradient = createGradient(ctx, gradientColors.combatDeaths);
    
    combatStatsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [i18n.totalKills, i18n.totalDeaths],
            datasets: [{
                data: [kills, deaths],
                backgroundColor: [killGradient, deathGradient],
                borderColor: [
                    hexToRgba(chartColors.combat.kills, 1),
                    hexToRgba(chartColors.combat.deaths, 1)
                ],
                borderWidth: 2,
                hoverOffset: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: chartColors.combat.text,
                        font: {
                            size: 14,
                            weight: 'bold'
                        },
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: chartColors.combat.text,
                    bodyColor: chartColors.combat.text,
                    borderColor: hexToRgba(chartColors.combat.kills, 0.45),
                    borderWidth: 1,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1500
            }
        }
    });
}

// Top 3 squadrons chart
function createTopSquadronsChart(squadrons) {
    const ctx = document.getElementById('topSquadronsChart').getContext('2d');
    
    if (topSquadronsChart) {
        topSquadronsChart.destroy();
    }
    
    const gradient = createGradient(ctx, gradientColors.squadrons);
    
    topSquadronsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: squadrons.map(s => s.name),
            datasets: [{
                label: i18n.squadronCredits,
                data: squadrons.map(s => s.credits),
                backgroundColor: gradient,
                borderColor: hexToRgba(chartColors.squadrons.main, 1),
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: chartColors.squadrons.text,
                    bodyColor: chartColors.squadrons.text,
                    borderColor: hexToRgba(chartColors.squadrons.main, 1),
                    borderWidth: 1,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `${i18n.totalCredits}: ${context.parsed.y.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: chartColors.squadrons.text,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    title: {
                        display: true,
                        text: i18n.squadronNames,
                        color: hexToRgba(chartColors.squadrons.main, 1),
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: hexToRgba(chartColors.squadrons.grid, 0.45),
                        borderDash: [5, 5]
                    },
                    ticks: {
                        color: chartColors.squadrons.text,
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: i18n.squadronCredits,
                        color: hexToRgba(chartColors.squadrons.main, 1),
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutBounce'
            }
        }
    });
}


// Player activity overview chart
function createPlayerActivityChart(daily_players) {
    const ctx = document.getElementById('playerActivityChart').getContext('2d');

    if (playerActivityChart) {
        playerActivityChart.destroy();
    }

    const gradient1 = createGradient(ctx, gradientColors.activity);

    // Process the dates and player counts
    const labels = daily_players.map(entry => {
        const date = new Date(entry.date);
        return date.toLocaleDateString();
    });

    const data = daily_players.map(entry => entry.player_count);

    playerActivityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: i18n.dailyPlayers,
                data: data,
                borderColor: hexToRgba(chartColors.activity.main, 1),
                backgroundColor: gradient1,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: hexToRgba(chartColors.activity.main, 1),
                pointBorderColor: chartColors.activity.text,
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: chartColors.activity.text,
                    bodyColor: chartColors.activity.text,
                    borderColor: hexToRgba(chartColors.activity.main, 1),
                    borderWidth: 1,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            return `${i18n.players}: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: hexToRgba(chartColors.activity.grid, 0.45),
                        borderDash: [5, 5]
                    },
                    ticks: {
                        color: chartColors.activity.text,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    title: {
                        display: true,
                        text: i18n.date,
                        color: hexToRgba(chartColors.activity.main, 1),
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: hexToRgba(chartColors.activity.grid, 0.45),
                        borderDash: [5, 5]
                    },
                    ticks: {
                        color: chartColors.activity.text,
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: i18n.numberOfPlayers,
                        color: hexToRgba(chartColors.activity.main, 1),
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
}

// Load stats on page load and refresh using the configured API interval
document.addEventListener('DOMContentLoaded', async () => {
    document.getElementById('topPilotsMetric')?.addEventListener('change', event => {
        loadTopPilotsChart(event.target.value);
    });
    loadServerStats();
    window.addEventListener('dcs-server-scope-change', loadServerStats);
    const refreshMs = window.dcsAPI ? await window.dcsAPI.getRefreshIntervalMs() : 600000;
    setInterval(loadServerStats, refreshMs);
});
</script>

<style>
main {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeInDown 0.8s ease-out;
}

.dashboard-header h1 {
    font-size: 2.5rem;
    color: #4CAF50;
    margin-bottom: 10px;
    text-shadow: 0 0 20px rgba(76, 175, 80, 0.5);
}

.dashboard-subtitle {
    color: #ccc;
    font-size: 1.1rem;
    opacity: 0.8;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 50px;
}

.stat-card {
    background: linear-gradient(135deg, #2c2c2c 0%, #1e1e1e 100%);
    border-radius: 16px;
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    opacity: 0;
    transform: translateY(20px);
}

.stat-card.pop-in {
    animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(76, 175, 80, 0.3);
    border-color: rgba(76, 175, 80, 0.5);
}

.stat-icon {
    font-size: 3rem;
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
    flex: 0 0 auto;
}

.stat-content {
    min-width: 0;
    flex: 1 1 auto;
}

.stat-content h3 {
    color: #ccc;
    font-size: 1rem;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    line-height: 1.15;
    max-width: 100%;
    overflow-wrap: anywhere;
    word-break: normal;
    hyphens: auto;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #4CAF50;
    margin: 0;
    text-shadow: 0 0 15px rgba(76, 175, 80, 0.5);
}

.charts-dashboard {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin: 40px auto;
    width: 100%;
    max-width: 100%;
    padding: 0 20px;
}

.api-attendance,
.api-insights {
    margin: 0 20px 40px;
}

.api-attendance .insight-cards {
    margin-bottom: 0;
}

.section-heading {
    margin-bottom: 18px;
}

.section-heading h2 {
    color: #4CAF50;
    margin: 0 0 6px;
}

.section-heading p {
    color: #ccc;
    margin: 0;
}

.insight-cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.insight-card,
.insight-panel {
    background: linear-gradient(135deg, #2c2c2c 0%, #1e1e1e 100%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.insight-card:hover,
.insight-panel:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(76, 175, 80, 0.3);
    border-color: rgba(76, 175, 80, 0.5);
}

.insight-card {
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 18px;
}

.insight-icon {
    font-size: 2.4rem;
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
    flex: 0 0 auto;
}

.insight-content {
    min-width: 0;
}

.insight-card span {
    color: #ccc;
    display: block;
    margin-bottom: 10px;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
}

.insight-card strong {
    color: #4CAF50;
    font-size: 2rem;
    text-shadow: 0 0 15px rgba(76, 175, 80, 0.5);
}

.insight-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
}

.insight-panel {
    padding: 24px;
}

.insight-panel h3 {
    color: #4CAF50;
    margin: 0 0 18px;
    text-align: center;
    text-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
}

.rank-list {
    display: grid;
    gap: 8px;
}

.rank-row {
    display: grid;
    grid-template-columns: 28px minmax(0, 1fr);
    gap: 8px 10px;
    align-items: center;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 10px;
}

.rank-number {
    color: #4CAF50;
    font-weight: 700;
}

.rank-row strong {
    color: #fff;
    overflow-wrap: anywhere;
}

.rank-row em {
    grid-column: 2;
    color: #aaa;
    font-style: normal;
}

@media (max-width: 968px) {
    .charts-dashboard {
        grid-template-columns: 1fr;
    }

    .insight-cards,
    .insight-grid {
        grid-template-columns: 1fr;
    }
}

.chart-container {
    background: linear-gradient(135deg, #2c2c2c 0%, #1e1e1e 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: fadeInUp 0.8s ease-out;
}

.chart-container.full-width {
    grid-column: 1 / -1;
}

.chart-container h2 {
    color: #4CAF50;
    font-size: 1.5rem;
    margin-bottom: 25px;
    text-align: center;
    text-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
}

.chart-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 25px;
}

.chart-card-header h2 {
    flex: 1;
    margin-bottom: 0;
}

.chart-metric-control {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: var(--card-muted-text);
    font-size: 0.9rem;
    font-weight: 700;
    white-space: nowrap;
}

.chart-metric-select {
    max-width: 190px;
}

.chart-metric-select select {
    width: 190px;
}

.chart-container canvas {
    max-height: 350px;
}

@media (max-width: 640px) {
    .chart-card-header {
        align-items: stretch;
        flex-direction: column;
    }

    .chart-card-header h2 {
        text-align: left;
    }

    .chart-metric-control {
        justify-content: space-between;
    }

    .chart-metric-control select {
        flex: 1;
        min-width: 0;
    }
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loader {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(76, 175, 80, 0.3);
    border-top-color: #4CAF50;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-overlay p {
    color: #4CAF50;
    margin-top: 20px;
    font-size: 1.2rem;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes popIn {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.no-data-message {
    text-align: center;
    color: #888;
    font-style: italic;
    margin-top: 20px;
    font-size: 0.9rem;
}

/* Chart info icon and tooltip styles */
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

.chart-container {
    position: relative;
}

.chart-container:hover {
    box-shadow: 0 12px 40px rgba(76, 175, 80, 0.3);
    border-color: rgba(76, 175, 80, 0.5);
}

.chart-container[title] {
    cursor: help;
}

/* Enhanced tooltip styling */
.chart-container:hover::after {
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
    white-space: normal;
    max-width: 300px;
    text-align: center;
    z-index: 1000;
    pointer-events: none;
    opacity: 0;
    animation: fadeIn 0.3s forwards;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.chart-container:hover::before {
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

@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .charts-dashboard {
        grid-template-columns: 1fr;
    }
    
    .dashboard-header h1 {
        font-size: 2rem;
    }
}

@media (max-width: 980px) and (min-width: 769px) {
    .stat-card {
        padding: 24px;
        gap: 16px;
    }

    .stat-icon {
        font-size: 2.6rem;
    }

    .stat-content h3 {
        font-size: 0.9rem;
    }

    .stat-number {
        font-size: 2.25rem;
    }
}
</style>

<?php include 'footer.php'; ?>
