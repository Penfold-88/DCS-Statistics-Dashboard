<?php 
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php'; 
?>
<?php require_once __DIR__ . '/site_features.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include 'nav.php'; ?>

<?php
// Check if this is a fresh install
$isConfigured = file_exists(__DIR__ . '/api_config.json') ||
                file_exists(__DIR__ . '/site-config/data/users.json');

// If homepage is disabled and site is configured, show message
if (!isFeatureEnabled('nav_home') && $isConfigured) {
    echo '<main><div class="alert" style="text-align: center; padding: 50px;">'
       . '<h2>Homepage Disabled</h2>'
       . '<p>The homepage is currently disabled.</p>'
       . '</div></main>';
    include 'footer.php';
    exit;
}

if (!$isConfigured):
?>
<main>
    <div class="welcome-container" style="max-width: 800px; margin: 50px auto; padding: 40px; background: var(--card-bg); border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center;">
        <h1 style="color: var(--primary-color); margin-bottom: 20px;">🎉 Welcome to DCS Statistics Dashboard!</h1>
        <p style="font-size: 1.2em; color: var(--text-secondary); margin-bottom: 30px;">
            It looks like this is your first time here. Let's get you set up!
        </p>
        
        <div style="background: rgba(0, 123, 255, 0.1); padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h2 style="color: var(--accent-primary); margin-bottom: 15px;">Quick Setup Guide</h2>
            <ol style="text-align: left; max-width: 500px; margin: 0 auto; line-height: 1.8;">
                <li>Create your admin account</li>
                <li>Configure your DCSServerBot API connection</li>
                <li>Customize your dashboard settings</li>
                <li>Start viewing your server statistics!</li>
            </ol>
        </div>
        
        <a href="./site-config/install.php" class="btn btn-primary" style="font-size: 1.2em; padding: 15px 40px; display: inline-block; text-decoration: none;">
            🚀 Start Setup
        </a>
        
        <p style="margin-top: 30px; font-size: 0.9em; color: var(--text-muted);">
            Need help? Check out the <a href="https://github.com/SocialOutcast-DCS/DCS-Statistics" target="_blank">documentation</a>
        </p>
    </div>
</main>
<?php else: ?>
<main>
    <div class="dashboard-header">
        <h1>DCS Statistics Dashboard</h1>
        <p class="dashboard-subtitle">Real-time server performance and player metrics</p>
    </div>
    
    <?php if (isFeatureEnabled('home_server_stats')): ?>
    <div class="stats-cards">
        <div class="stat-card" id="totalPlayersCard">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>Total Players</h3>
                <p class="stat-number" id="totalPlayers">-</p>
            </div>
        </div>
        
        <div class="stat-card" id="totalPlaytimeCard">
            <div class="stat-icon">✈️</div>
            <div class="stat-content">
                <h3>Total Playtime (hrs)</h3>
                <p class="stat-number" id="totalPlaytime">-</p>
            </div>
        </div>
        
        <div class="stat-card" id="avgPlaytimeCard">
            <div class="stat-icon">🕐</div>
            <div class="stat-content">
                <h3>Average Playtime (mins)</h3>
                <p class="stat-number" id="avgPlaytime">-</p>
            </div>
        </div>
        
        <div class="stat-card" id="totalSortiesCard">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>Total Sorties</h3>
                <p class="stat-number" id="totalSorties">-</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="charts-dashboard">
        <?php if (isFeatureEnabled('home_top_pilots')): ?>
        <div class="chart-container" title="Shows the top 5 pilots ranked by their kills">
            <h2>Top 5 Pilots <span class="chart-info">ⓘ</span></h2>
            <canvas id="topPilotsChart"></canvas>
            <p class="no-data-message" id="topPilotsNoData" style="display: none;">No mission data available yet</p>
        </div>
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('home_mission_stats')): ?>
        <div class="chart-container" title="Overview of total server-wide kills and deaths in combat">
            <h2>Server Combat Statistics <span class="chart-info">ⓘ</span></h2>
            <canvas id="combatStatsChart"></canvas>
        </div>
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('squadrons_enabled') && isFeatureEnabled('credits_enabled') && isFeatureEnabled('home_top_squadrons')): ?>
        <div class="chart-container" title="Shows the top 3 squadrons based on member activity and performance">
            <h2>Top 3 Most Active Squadrons <span class="chart-info">ⓘ</span></h2>
            <canvas id="topSquadronsChart"></canvas>
            <p class="no-data-message" id="squadronsNoData" style="display: none;">No squadron data available yet</p>
        </div>
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('home_player_activity')): ?>
        <div class="chart-container full-width" title="Displays player activity trends over time showing peak hours and player engagement">
            <h2>Player Activity Overview <span class="chart-info">ⓘ</span></h2>
            <canvas id="playerActivityChart"></canvas>
        </div>
        <?php endif; ?>
    </div>
    
    <div id="loading-overlay" class="loading-overlay">
        <div class="loader"></div>
        <p>Loading server statistics...</p>
    </div>
</main>

<script>
// Chart instances
let topPilotsChart = null;
let combatStatsChart = null;
let playerActivityChart = null;
let topSquadronsChart = null;

// Chart configuration with enhanced dark theme
const chartColors = {
    primary: 'rgba(76, 175, 80, 0.8)',
    secondary: 'rgba(33, 150, 243, 0.8)',
    danger: 'rgba(244, 67, 54, 0.8)',
    warning: 'rgba(255, 193, 7, 0.8)',
    info: 'rgba(0, 188, 212, 0.8)',
    purple: 'rgba(156, 39, 176, 0.8)',
    pink: 'rgba(233, 30, 99, 0.8)'
};

const gradientColors = {
    primary: ['rgba(76, 175, 80, 1)', 'rgba(76, 175, 80, 0.2)'],
    secondary: ['rgba(33, 150, 243, 1)', 'rgba(33, 150, 243, 0.2)'],
    danger: ['rgba(244, 67, 54, 1)', 'rgba(244, 67, 54, 0.2)'],
    warning: ['rgba(255, 193, 7, 1)', 'rgba(255, 193, 7, 0.2)']
};

// Load server statistics
async function loadServerStats() {
    try {
        // Use the client-side API
        const data = await window.dcsAPI.getServerStats();
        
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
        if (data.top5Pilots && data.top5Pilots.length > 0) {
            createTopPilotsChart(data.top5Pilots);
            document.getElementById('topPilotsNoData').style.display = 'none';
        } else {
            document.getElementById('topPilotsChart').style.display = 'none';
            document.getElementById('topPilotsNoData').style.display = 'block';
        }
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('home_mission_stats')): ?>
        createCombatStatsChart(data.totalKills || 0, data.totalDeaths || 0);
        <?php endif; ?>
        
        <?php if (isFeatureEnabled('squadrons_enabled') && isFeatureEnabled('home_top_squadrons')): ?>
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
function createTopPilotsChart(pilots) {
    const ctx = document.getElementById('topPilotsChart').getContext('2d');
    
    if (topPilotsChart) {
        topPilotsChart.destroy();
    }
    
    const gradient = createGradient(ctx, gradientColors.primary);
    
    topPilotsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: pilots.map(p => p.nick),
            datasets: [{
                label: 'Kills',
                data: pilots.map(p => p.kills),
                backgroundColor: gradient,
                borderColor: 'rgba(76, 175, 80, 1)',
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
                    titleColor: '#4CAF50',
                    bodyColor: '#fff',
                    borderColor: '#4CAF50',
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
                            return `Kills: ${context.parsed.y.toLocaleString()}`;
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
                        color: '#ccc',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Pilot Names',
                        color: '#4CAF50',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)',
                        borderDash: [5, 5]
                    },
                    ticks: {
                        color: '#ccc',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: 'Number of Kills',
                        color: '#4CAF50',
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
    
    const killGradient = createGradient(ctx, gradientColors.secondary);
    const deathGradient = createGradient(ctx, gradientColors.danger);
    
    combatStatsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Total Kills', 'Total Deaths'],
            datasets: [{
                data: [kills, deaths],
                backgroundColor: [killGradient, deathGradient],
                borderColor: ['rgba(33, 150, 243, 1)', 'rgba(244, 67, 54, 1)'],
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
                        color: '#ccc',
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
                    titleColor: '#fff',
                    bodyColor: '#ccc',
                    borderColor: '#444',
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
    
    const gradient = createGradient(ctx, gradientColors.warning);
    
    topSquadronsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: squadrons.map(s => s.name),
            datasets: [{
                label: 'Squadron Credits',
                data: squadrons.map(s => s.credits),
                backgroundColor: gradient,
                borderColor: 'rgba(255, 193, 7, 1)',
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
                    titleColor: '#FFD700',
                    bodyColor: '#fff',
                    borderColor: '#FFD700',
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
                            return `Total Credits: ${context.parsed.y.toLocaleString()}`;
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
                        color: '#ccc',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Squadron Names',
                        color: '#FFD700',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)',
                        borderDash: [5, 5]
                    },
                    ticks: {
                        color: '#ccc',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: 'Squadron Credits',
                        color: '#FFD700',
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

    const gradient1 = createGradient(ctx, gradientColors.primary);

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
                label: 'Daily Players',
                data: data,
                borderColor: 'rgba(76, 175, 80, 1)',
                backgroundColor: gradient1,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(76, 175, 80, 1)',
                pointBorderColor: '#fff',
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
                    titleColor: '#4CAF50',
                    bodyColor: '#fff',
                    borderColor: '#4CAF50',
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
                            return `Players: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)',
                        borderDash: [5, 5]
                    },
                    ticks: {
                        color: '#ccc',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Date',
                        color: '#4CAF50',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)',
                        borderDash: [5, 5]
                    },
                    ticks: {
                        color: '#ccc',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: 'Number of Players',
                        color: '#4CAF50',
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

// Load stats on page load
document.addEventListener('DOMContentLoaded', loadServerStats);

// Refresh stats every 30 seconds
setInterval(loadServerStats, 30000);
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
}

.stat-content h3 {
    color: #ccc;
    font-size: 1rem;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
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

@media (max-width: 968px) {
    .charts-dashboard {
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

.chart-container canvas {
    max-height: 350px;
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
</style>

<?php endif; ?>
<?php include 'footer.php'; ?>