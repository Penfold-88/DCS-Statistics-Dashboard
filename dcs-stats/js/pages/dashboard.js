const dashboardConfig = window.DCS_DASHBOARD_CONFIG || {};
const i18n = dashboardConfig.i18n || {};

// Chart instances
let topPilotsChart = null;
let combatStatsChart = null;
let playerActivityChart = null;
let topSquadronsChart = null;
let latestTopPilots = [];

const homepageChartTheme = dashboardConfig.chartTheme || {};
const publicDateFormat = dashboardConfig.publicDateFormat || 'd/m/Y';
const homepageDataNeeds = dashboardConfig.dataNeeds || {};
const homepageFeatures = dashboardConfig.features || {};

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

function formatPublicDate(date) {
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
        if (homepageFeatures.serverStats) {
            animateNumber('totalPlayers', data.totalPlayers);
            animateNumber('totalPlaytime', data.totalPlaytime);
            animateNumber('avgPlaytime', data.avgPlaytime / 60);
            animateNumber('totalSorties', data.totalSorties);
        }
        
        // Create charts with empty data handling
        if (homepageFeatures.topPilots) {
            await loadTopPilotsChart(document.getElementById('topPilotsMetric')?.value || 'kills', data.top5Pilots || []);
        }
        
        if (homepageFeatures.missionStats) {
            createCombatStatsChart(data.totalKills || 0, data.totalDeaths || 0);
        }
        
        if (homepageFeatures.topSquadrons) {
            loadTopSquadronsChart();
        }
        
        if (homepageFeatures.playerActivity) {
            createPlayerActivityChart(data.activityLastWeek || []);
        }

        if (homepageFeatures.apiInsights) {
            renderApiInsights(data.attendance || {});
        }
        
        // Hide loading overlay
        document.getElementById('loading-overlay').style.display = 'none';
        
        // Add pop animations to cards
        if (homepageFeatures.serverStats) {
            document.querySelectorAll('.stat-card').forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('pop-in');
                }, index * 100);
            });
        }
        
    } catch (error) {
        console.error('Error fetching server stats:', error);
        document.getElementById('loading-overlay').style.display = 'none';
    }
}

async function loadTopSquadronsChart() {
    const canvas = document.getElementById('topSquadronsChart');
    const noData = document.getElementById('squadronsNoData');
    if (!canvas || !noData || !window.dcsAPI?.getTopSquadrons) return;

    try {
        const squadrons = await window.dcsAPI.getTopSquadrons(3);
        if (squadrons.length > 0) {
            createTopSquadronsChart(squadrons);
            canvas.style.display = 'block';
            noData.style.display = 'none';
        } else {
            canvas.style.display = 'none';
            noData.style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading top squadrons chart:', error);
        canvas.style.display = 'none';
        noData.style.display = 'block';
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
        return Number.isNaN(date.getTime()) ? String(entry.date || '') : formatPublicDate(date);
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
