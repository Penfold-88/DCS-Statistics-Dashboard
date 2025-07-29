<?php include 'header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include 'nav.php'; ?>

<main>
    <div class="dashboard-header">
        <h1>DCS Server Statistics Dashboard</h1>
        <p class="dashboard-subtitle">Real-time server performance and player metrics</p>
    </div>
    
    <div class="stats-cards">
        <div class="stat-card" id="totalPlayersCard">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>Total Players</h3>
                <p class="stat-number" id="totalPlayers">-</p>
            </div>
        </div>
        
        <div class="stat-card" id="totalKillsCard">
            <div class="stat-icon">🎯</div>
            <div class="stat-content">
                <h3>Server Kills</h3>
                <p class="stat-number" id="totalKills">-</p>
            </div>
        </div>
        
        <div class="stat-card" id="totalDeathsCard">
            <div class="stat-icon">💥</div>
            <div class="stat-content">
                <h3>Server Deaths</h3>
                <p class="stat-number" id="totalDeaths">-</p>
            </div>
        </div>
        
        <div class="stat-card" id="kdRatioCard">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>K/D Ratio</h3>
                <p class="stat-number" id="kdRatio">-</p>
            </div>
        </div>
    </div>
    
    <div class="charts-dashboard">
        <div class="chart-container">
            <h2>Top 5 Most Active Pilots</h2>
            <canvas id="topPilotsChart"></canvas>
            <p class="no-data-message" id="topPilotsNoData" style="display: none;">No mission data available yet</p>
        </div>
        
        <div class="chart-container">
            <h2>Server Combat Statistics</h2>
            <canvas id="combatStatsChart"></canvas>
        </div>
        
        <div class="chart-container">
            <h2>Top 3 Most Active Squadrons</h2>
            <canvas id="topSquadronsChart"></canvas>
            <p class="no-data-message" id="squadronsNoData" style="display: none;">No squadron data available yet</p>
        </div>
        
        <div class="chart-container full-width">
            <h2>Player Activity Overview</h2>
            <canvas id="playerActivityChart"></canvas>
        </div>
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
        const response = await fetch('get_server_stats.php');
        const data = await response.json();
        
        if (data.error) {
            console.error('Error loading stats:', data.error);
            document.getElementById('loading-overlay').style.display = 'none';
            return;
        }
        
        // Update stat cards with animation
        animateNumber('totalPlayers', data.totalPlayers);
        animateNumber('totalKills', data.totalKills);
        animateNumber('totalDeaths', data.totalDeaths);
        
        // Calculate K/D ratio
        const kdRatio = data.totalDeaths > 0 ? (data.totalKills / data.totalDeaths).toFixed(2) : data.totalKills;
        document.getElementById('kdRatio').textContent = kdRatio;
        
        // Create charts with empty data handling
        if (data.top5Pilots && data.top5Pilots.length > 0) {
            createTopPilotsChart(data.top5Pilots);
            document.getElementById('topPilotsNoData').style.display = 'none';
        } else {
            document.getElementById('topPilotsChart').style.display = 'none';
            document.getElementById('topPilotsNoData').style.display = 'block';
        }
        
        createCombatStatsChart(data.totalKills || 0, data.totalDeaths || 0);
        
        if (data.top3Squadrons && data.top3Squadrons.length > 0) {
            createTopSquadronsChart(data.top3Squadrons);
            document.getElementById('squadronsNoData').style.display = 'none';
        } else {
            document.getElementById('topSquadronsChart').style.display = 'none';
            document.getElementById('squadronsNoData').style.display = 'block';
        }
        
        createPlayerActivityChart(data.totalPlayers || 0, data.top5Pilots || []);
        
        // Hide loading overlay
        document.getElementById('loading-overlay').style.display = 'none';
        
        // Add pop animations to cards
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('pop-in');
            }, index * 100);
        });
        
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
            labels: pilots.map(p => p.name),
            datasets: [{
                label: 'Server Visits',
                data: pilots.map(p => p.visits),
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
                            return `Visits: ${context.parsed.y.toLocaleString()}`;
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
                        text: 'Number of Server Visits',
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
                label: 'Squadron Visits',
                data: squadrons.map(s => s.visits),
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
                            return `Total Visits: ${context.parsed.y.toLocaleString()}`;
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
                        text: 'Combined Member Visits',
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
function createPlayerActivityChart(totalPlayers, top5Pilots) {
    const ctx = document.getElementById('playerActivityChart').getContext('2d');
    
    if (playerActivityChart) {
        playerActivityChart.destroy();
    }
    
    const labels = ['Total Registered', 'Active Players', 'Top 5 Combined Visits'];
    const activePlayers = top5Pilots.reduce((sum, p) => sum + (p.visits > 0 ? 1 : 0), 0);
    const top5Visits = top5Pilots.reduce((sum, p) => sum + p.visits, 0);
    
    const gradient1 = createGradient(ctx, gradientColors.primary);
    const gradient2 = createGradient(ctx, gradientColors.warning);
    const gradient3 = createGradient(ctx, gradientColors.secondary);
    
    playerActivityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Player Metrics',
                data: [totalPlayers, activePlayers, top5Visits],
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
                    displayColors: false
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
                        text: 'Player Categories',
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
    max-width: 1200px;
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

<?php include 'footer.php'; ?>