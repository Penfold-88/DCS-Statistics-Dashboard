<?php include 'header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include 'nav.php'; ?>

<main>
    <h2>Pilot Statistics</h2>
    
    <div class="search-container">
        <input type="text" id="playerSearchInput" placeholder="Search for a pilot..." />
        <button onclick="searchForPlayers()">Search</button>
    </div>
    
    <div id="multiple-results" style="display: none;">
        <h3 style="text-align: center; color: #ccc;">Multiple pilots found. Please select one:</h3>
        <div id="results-list" class="results-list"></div>
    </div>
    
    <div id="search-results" style="display: none;">
        <div id="pilot-card" class="pilot-card">
            <h3 id="pilot-name"></h3>
            <div class="pilot-stats">
                <div class="stat-group">
                    <h4>Combat Statistics</h4>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Kills:</span>
                            <span class="stat-value" id="pilot-kills">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Sorties:</span>
                            <span class="stat-value" id="pilot-sorties">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Takeoffs:</span>
                            <span class="stat-value" id="pilot-takeoffs">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Landings:</span>
                            <span class="stat-value" id="pilot-landings">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Carrier Traps:</span>
                            <span class="stat-value" id="pilot-traps">0</span>
                        </div>
                        <div class="stat-item" id="trap-score-item" style="display: none;">
                            <span class="stat-label">Avg Trap Score:</span>
                            <span class="stat-value" id="pilot-trap-score">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Crashes:</span>
                            <span class="stat-value" id="pilot-crashes">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Ejections:</span>
                            <span class="stat-value" id="pilot-ejections">0</span>
                        </div>
                    </div>
                </div>
                
                <div class="stat-group">
                    <h4>Credits & Squadron</h4>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Credits:</span>
                            <span class="stat-value" id="pilot-credits">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Most Used Aircraft:</span>
                            <span class="stat-value" id="pilot-aircraft">Unknown</span>
                        </div>
                        <div class="stat-item" id="squadron-info">
                            <span class="stat-label">Squadron:</span>
                            <span class="stat-value" id="pilot-squadron">None</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="charts-container">
                <div class="chart-wrapper">
                    <h4>Combat Performance</h4>
                    <canvas id="combatChart"></canvas>
                </div>
                <div class="chart-wrapper">
                    <h4>Flight Statistics</h4>
                    <canvas id="flightChart"></canvas>
                </div>
                <div class="chart-wrapper" id="aircraftChartWrapper" style="display: none;">
                    <h4>Aircraft Usage</h4>
                    <canvas id="aircraftChart"></canvas>
                </div>
                <div class="chart-wrapper" id="trapScoresChartWrapper" style="display: none;">
                    <h4>Carrier Landing Performance</h4>
                    <canvas id="trapScoresChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div id="no-results" style="display: none; text-align: center; color: #ccc; margin-top: 30px;">
        <p id="no-results-message">No pilot found with that name. Please check the spelling and try again.</p>
    </div>
    
    <div id="loading" style="display: none; text-align: center; color: #ccc; margin-top: 30px;">
        <p>Searching...</p>
    </div>
</main>

<script>
async function searchForPlayers() {
    const searchInput = document.getElementById('playerSearchInput');
    const searchTerm = searchInput.value.trim();
    
    if (!searchTerm) {
        alert('Please enter a pilot name to search.');
        return;
    }
    
    // Hide all sections
    document.getElementById('search-results').style.display = 'none';
    document.getElementById('multiple-results').style.display = 'none';
    document.getElementById('no-results').style.display = 'none';
    document.getElementById('loading').style.display = 'block';
    
    try {
        // Search for players
        const searchResponse = await fetch(`search_players.php?search=${encodeURIComponent(searchTerm)}`);
        const searchData = await searchResponse.json();
        
        document.getElementById('loading').style.display = 'none';
        
        if (searchData.error || searchData.count === 0) {
            document.getElementById('no-results-message').textContent = 
                'No pilots with recorded statistics found matching that name.';
            document.getElementById('no-results').style.display = 'block';
            return;
        }
        
        if (searchData.count === 1) {
            // Single result - load directly
            await loadPilotStats(searchData.results[0].name);
        } else {
            // Multiple results - show selection
            showMultipleResults(searchData.results);
        }
        
    } catch (error) {
        console.error('Error searching for pilots:', error);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('no-results').style.display = 'block';
    }
}

function showMultipleResults(results) {
    const resultsList = document.getElementById('results-list');
    resultsList.innerHTML = '';
    
    results.forEach(pilot => {
        const resultItem = document.createElement('div');
        resultItem.className = 'result-item';
        resultItem.textContent = pilot.name;
        resultItem.onclick = () => {
            document.getElementById('multiple-results').style.display = 'none';
            document.getElementById('loading').style.display = 'block';
            loadPilotStats(pilot.name);
        };
        resultsList.appendChild(resultItem);
    });
    
    document.getElementById('multiple-results').style.display = 'block';
}

async function loadPilotStats(playerName) {
    document.getElementById('multiple-results').style.display = 'none';
    
    try {
        // Get player stats
        const statsResponse = await fetch(`get_player_stats.php?name=${encodeURIComponent(playerName)}`);
        const statsData = await statsResponse.json();
        
        if (statsData.error) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('no-results').style.display = 'block';
            return;
        }
        
        // Get credits data
        let credits = 0;
        try {
            const creditsResponse = await fetch('get_credits.php');
            const creditsData = await creditsResponse.json();
            const playerCredits = creditsData.find(p => p.name.toLowerCase() === playerName.toLowerCase());
            credits = playerCredits ? playerCredits.credits : 0;
        } catch (e) {
            console.warn('Could not load credits data:', e);
        }
        
        // Get squadron data
        let squadron = 'None';
        let squadronLogo = null;
        try {
            // Try to get squadron data from squadrons endpoint
            const squadronResponse = await fetch('data/squadron_members.json');
            if (squadronResponse.ok) {
                const squadronText = await squadronResponse.text();
                const squadronMembers = squadronText.trim().split('\n').map(line => JSON.parse(line));
                
                // Get player UCID to match with squadron
                const playersResponse = await fetch('data/players.json');
                if (playersResponse.ok) {
                    const playersText = await playersResponse.text();
                    const players = playersText.trim().split('\n').map(line => JSON.parse(line));
                    const player = players.find(p => p.name.toLowerCase() === playerName.toLowerCase());
                    
                    if (player) {
                        const memberData = squadronMembers.find(m => m.player_ucid === player.ucid);
                        if (memberData) {
                            // Get squadron name and logo
                            const squadronsResponse = await fetch('data/squadrons.json');
                            if (squadronsResponse.ok) {
                                const squadronsText = await squadronsResponse.text();
                                const squadrons = squadronsText.trim().split('\n').map(line => JSON.parse(line));
                                const squadronInfo = squadrons.find(s => s.id === memberData.squadron_id);
                                if (squadronInfo) {
                                    squadron = squadronInfo.name || 'Unknown Squadron';
                                    squadronLogo = squadronInfo.image_url || null;
                                }
                            }
                        }
                    }
                }
            }
        } catch (e) {
            console.warn('Could not load squadron data:', e);
        }
        
        // Populate the results
        document.getElementById('pilot-name').textContent = statsData.name;
        document.getElementById('pilot-kills').textContent = statsData.kills || 0;
        document.getElementById('pilot-sorties').textContent = statsData.sorties || 0;
        document.getElementById('pilot-takeoffs').textContent = statsData.takeoffs || 0;
        document.getElementById('pilot-landings').textContent = statsData.landings || 0;
        document.getElementById('pilot-traps').textContent = statsData.traps || 0;
        
        // Show average trap score if there are traps
        if (statsData.traps > 0 && statsData.avgTrapScore !== undefined) {
            document.getElementById('trap-score-item').style.display = 'flex';
            document.getElementById('pilot-trap-score').textContent = statsData.avgTrapScore.toFixed(2);
        }
        
        document.getElementById('pilot-crashes').textContent = statsData.crashes || 0;
        document.getElementById('pilot-ejections').textContent = statsData.ejections || 0;
        document.getElementById('pilot-credits').textContent = credits;
        document.getElementById('pilot-aircraft').textContent = statsData.mostUsedAircraft || 'Unknown';
        
        // Update squadron info with logo if available
        const squadronInfoDiv = document.getElementById('squadron-info');
        if (squadronLogo && squadron !== 'None') {
            squadronInfoDiv.innerHTML = `
                <span class="stat-label">Squadron:</span>
                <div class="squadron-display">
                    <img src="${squadronLogo}" alt="${squadron}" class="squadron-logo">
                    <span class="stat-value">${squadron}</span>
                </div>
            `;
        } else {
            document.getElementById('pilot-squadron').textContent = squadron;
        }
        
        // Show results
        document.getElementById('loading').style.display = 'none';
        document.getElementById('search-results').style.display = 'block';
        
        // Create charts
        createCombatChart(statsData);
        createFlightChart(statsData);
        if (statsData.aircraftUsage && statsData.aircraftUsage.length > 0) {
            createAircraftChart(statsData.aircraftUsage);
        }
        if (statsData.trapScores && statsData.trapScores.length > 0) {
            createTrapScoresChart(statsData.trapScores);
        }
        
    } catch (error) {
        console.error('Error loading pilot stats:', error);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('no-results').style.display = 'block';
    }
}

// Chart instances
let combatChart = null;
let flightChart = null;
let aircraftChart = null;
let trapScoresChart = null;

// Chart configuration with dark theme
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#ccc',
                font: {
                    size: 12
                }
            }
        },
        tooltip: {
            backgroundColor: '#1e1e1e',
            titleColor: '#4CAF50',
            bodyColor: '#ccc',
            borderColor: '#444',
            borderWidth: 1
        }
    },
    scales: {
        x: {
            ticks: {
                color: '#ccc'
            },
            grid: {
                color: '#333',
                borderColor: '#444'
            },
            title: {
                display: true,
                text: 'Statistics',
                color: '#4CAF50',
                font: {
                    size: 14,
                    weight: 'bold'
                }
            }
        },
        y: {
            ticks: {
                color: '#ccc'
            },
            grid: {
                color: '#333',
                borderColor: '#444'
            },
            title: {
                display: true,
                text: 'Count',
                color: '#4CAF50',
                font: {
                    size: 14,
                    weight: 'bold'
                }
            }
        }
    }
};

function createCombatChart(statsData) {
    const ctx = document.getElementById('combatChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (combatChart) {
        combatChart.destroy();
    }
    
    combatChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Kills', 'Sorties'],
            datasets: [{
                label: 'Combat Stats',
                data: [statsData.kills || 0, statsData.sorties || 0],
                backgroundColor: [
                    'rgba(76, 175, 80, 0.6)',
                    'rgba(33, 150, 243, 0.6)'
                ],
                borderColor: [
                    'rgba(76, 175, 80, 1)',
                    'rgba(33, 150, 243, 1)'
                ],
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
                        display: true,
                        text: 'Combat Metrics',
                        color: '#4CAF50',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    ...chartOptions.scales.y,
                    title: {
                        display: true,
                        text: 'Number of Events',
                        color: '#4CAF50',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
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
    const traps = statsData.traps || 0;
    
    // Adjust labels and data based on whether pilot has traps
    const labels = traps > 0 
        ? ['Land Landings', 'Carrier Traps', 'Crashes', 'Ejections', 'In Flight']
        : ['Successful Landings', 'Crashes', 'Ejections', 'In Flight'];
    
    const data = traps > 0
        ? [
            landings - traps,  // Land landings (total landings minus traps)
            traps,             // Carrier traps
            crashes,
            ejections,
            Math.max(0, takeoffs - landings - crashes - ejections)
          ]
        : [
            landings,
            crashes,
            ejections,
            Math.max(0, takeoffs - landings - crashes - ejections)
          ];
    
    flightChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: traps > 0 
                    ? [
                        'rgba(76, 175, 80, 0.6)',     // Land landings - green
                        'rgba(33, 150, 243, 0.6)',     // Carrier traps - blue
                        'rgba(244, 67, 54, 0.6)',      // Crashes - red
                        'rgba(255, 152, 0, 0.6)',      // Ejections - orange
                        'rgba(158, 158, 158, 0.6)'     // In flight - gray
                      ]
                    : [
                        'rgba(76, 175, 80, 0.6)',      // Landings - green
                        'rgba(244, 67, 54, 0.6)',      // Crashes - red
                        'rgba(255, 152, 0, 0.6)',      // Ejections - orange
                        'rgba(158, 158, 158, 0.6)'     // In flight - gray
                      ],
                borderColor: traps > 0
                    ? [
                        'rgba(76, 175, 80, 1)',
                        'rgba(33, 150, 243, 1)',
                        'rgba(244, 67, 54, 1)',
                        'rgba(255, 152, 0, 1)',
                        'rgba(158, 158, 158, 1)'
                      ]
                    : [
                        'rgba(76, 175, 80, 1)',
                        'rgba(244, 67, 54, 1)',
                        'rgba(255, 152, 0, 1)',
                        'rgba(158, 158, 158, 1)'
                      ],
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
        'rgba(76, 175, 80, 0.6)',
        'rgba(33, 150, 243, 0.6)',
        'rgba(255, 193, 7, 0.6)',
        'rgba(233, 30, 99, 0.6)',
        'rgba(156, 39, 176, 0.6)'
    ];
    
    const borderColors = colors.map(c => c.replace('0.6', '1'));
    
    aircraftChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Times Used',
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
                        display: true,
                        text: 'Times Used',
                        color: '#4CAF50',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    ...chartOptions.scales.y,
                    title: {
                        display: true,
                        text: 'Aircraft Type',
                        color: '#4CAF50',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
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
        'rgba(76, 175, 80, 0.6)',   // OK - green (perfect)
        'rgba(255, 193, 7, 0.6)',   // Fair - yellow
        'rgba(158, 158, 158, 0.6)', // No Grade - gray
        'rgba(255, 152, 0, 0.6)',   // Cut - orange (dangerous)
        'rgba(244, 67, 54, 0.6)'    // Wave Off - red
    ];
    
    const borderColors = colors.map(c => c.replace('0.6', '1'));
    
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
                        display: true,
                        text: 'Landing Grade',
                        color: '#4CAF50',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
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
                        display: true,
                        text: 'Number of Carrier Traps',
                        color: '#4CAF50',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
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
</script>

<style>
main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.pilot-card {
    background-color: #2c2c2c;
    border-radius: 12px;
    padding: 30px;
    margin: 20px auto;
    max-width: 800px;
    color: #fff;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
}

.pilot-card h3 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 1.8rem;
    color: #4CAF50;
}

.stat-group {
    margin-bottom: 30px;
}

.stat-group h4 {
    color: #ccc;
    margin-bottom: 15px;
    border-bottom: 1px solid #444;
    padding-bottom: 5px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background-color: #1e1e1e;
    border-radius: 5px;
}

.stat-label {
    color: #ccc;
}

.stat-value {
    color: #fff;
    font-weight: bold;
}

.search-container {
    text-align: center;
    margin: 30px 0;
}

.search-container input {
    padding: 12px;
    width: 300px;
    max-width: 80%;
    font-size: 16px;
    border: 1px solid #444;
    border-radius: 5px;
    background-color: #1f2b34;
    color: white;
    margin-right: 10px;
}

.search-container button {
    padding: 12px 20px;
    font-size: 16px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.search-container button:hover {
    background-color: #45a049;
}

.results-list {
    max-width: 600px;
    margin: 20px auto;
    background-color: #2c2c2c;
    border-radius: 8px;
    padding: 10px;
    max-height: 300px;
    overflow-y: auto;
}

.result-item {
    padding: 12px 20px;
    margin: 5px;
    background-color: #1e1e1e;
    border-radius: 5px;
    cursor: pointer;
    color: #fff;
    transition: background-color 0.3s;
}

.result-item:hover {
    background-color: #3a3a3a;
    color: #4CAF50;
}

.squadron-display {
    display: flex;
    align-items: center;
    gap: 10px;
}

.squadron-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 4px;
}

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
</style>

<?php include 'footer.php'; ?>
