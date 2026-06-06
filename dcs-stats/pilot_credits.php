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
<?php include 'nav.php'; ?>

<?php if (!isFeatureEnabled('credits_enabled')): ?>
<main>
    <div class="alert" style="text-align: center; padding: 50px;">
        <h2><?php echo htmlspecialchars(dcs_t('credits.disabled_title')); ?></h2>
        <p><?php echo htmlspecialchars(dcs_t('credits.disabled_message')); ?></p>
    </div>
</main>
<?php include 'footer.php'; exit; ?>
<?php endif; ?>

<main>
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars(dcs_t('credits.title')); ?></h1>
        <p class="dashboard-subtitle"><?php echo htmlspecialchars(dcs_t('credits.subtitle')); ?></p>
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
        <h3 style="text-align: center; color: #ccc; margin-bottom: 20px;"><?php echo htmlspecialchars(dcs_t('credits.search_results')); ?></h3>
        <div id="results-list" class="results-list"></div>
    </div>

    <!-- Loading indicator -->
    <div id="loading" class="loading-spinner" style="display: none;">
        <p><?php echo htmlspecialchars(dcs_t('credits.searching')); ?></p>
    </div>
    
    <!-- Credits display -->
    <div id="credits-display" style="display: none;">
        <div id="pilot-card" class="pilot-card">
            <h3 id="pilot-display-name"></h3>
            <div class="pilot-stats">
                <div class="stat-group">
                    <h4><?php echo htmlspecialchars(dcs_t('credits.information')); ?></h4>
                    <div class="stats-grid">
                        <div class="stat-item credits-stat-item">
                            <span class="stat-label"><?php echo htmlspecialchars(dcs_t('credits.current_balance')); ?>:</span>
                            <span class="stat-value credits-value" id="credits-value">0</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="search-again">
                <button onclick="searchAgain()" class="search-container button"><?php echo htmlspecialchars(dcs_t('credits.search_another')); ?></button>
            </div>
        </div>
    </div>
    
    <!-- No results message -->
    <div id="no-results" class="no-results" style="display: none;">
        <p id="no-results-message"><?php echo htmlspecialchars(dcs_t('credits.no_data')); ?></p>
        <button onclick="searchAgain()" class="btn-secondary"><?php echo htmlspecialchars(dcs_t('credits.try_another')); ?></button>
    </div>
</main>

<style>
/* Credits-specific styles using unified pilot card design */

.loading-spinner {
    text-align: center;
    padding: 50px;
    color: #4CAF50;
    font-size: 1.2rem;
}

.credits-stat-item .stat-value {
    font-size: 2rem;
    color: #4CAF50;
    text-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
}

.search-again {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #444;
}

.no-results {
    text-align: center;
    padding: 50px;
    color: #ccc;
    background-color: #2c2c2c;
    border-radius: 12px;
    margin: 20px auto;
    max-width: 600px;
}

#no-results-message {
    margin-bottom: 20px;
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
        gap: 15px;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        padding: 12px;
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
    
    /* Credits value stays large on mobile */
    .credits-stat-item .stat-value {
        font-size: 1.8rem;
    }
    
    /* Search again button */
    .search-again {
        padding: 15px;
    }
    
    .search-again button {
        width: 100%;
        padding: 12px;
        min-height: 44px;
    }
    
    /* No results section */
    .no-results {
        padding: 20px;
    }
    
    .no-results button {
        width: 100%;
        margin-top: 15px;
        padding: 12px;
        min-height: 44px;
    }
    
    /* Dashboard header mobile */
    .dashboard-header h1 {
        font-size: 1.8rem;
    }
    
    .dashboard-subtitle {
        font-size: 0.9rem;
        padding: 0 10px;
    }
    
    /* Loading spinner */
    .loading-spinner {
        padding: 30px;
        font-size: 1rem;
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
        padding: 10px;
    }
    
    .credits-stat-item .stat-value {
        font-size: 1.5rem;
    }
}

/* Touch-friendly hover states */
@media (hover: none) and (pointer: coarse) {
    button:hover {
        transform: none;
    }
    
    button:active {
        transform: scale(0.95);
    }
}

</style>

<?php tableResponsiveStyles(); ?>

<script>
const i18n = <?php echo json_encode([
    'enterName' => dcs_t('pilot.enter_name'),
    'noMatches' => dcs_t('pilot.no_matches'),
    'checkSpelling' => dcs_t('pilot.check_spelling'),
    'usePartial' => dcs_t('pilot.use_partial'),
    'searchStart' => dcs_t('pilot.search_start'),
    'searchError' => dcs_t('pilot.search_error'),
    'noPilotData' => dcs_t('credits.no_pilot_data'),
    'noTransactions' => dcs_t('credits.no_transactions'),
    'loadError' => dcs_t('credits.load_error'),
    'tryLater' => dcs_t('credits.try_later')
], JSON_UNESCAPED_UNICODE); ?>;

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
            await loadPilotCredits(searchData.results[0]);
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

async function loadPilotCredits(pilot) {
    try {
        // Call credits endpoint with exact name
        const basePath = window.DCS_CONFIG ? window.DCS_CONFIG.basePath : '';
        const buildUrl = (path) => basePath ? `${basePath}/${path}` : path;
        const response = await fetch(buildUrl('api_proxy.php?endpoint=' + encodeURIComponent('/credits') + '&method=POST'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nick: pilot.nick,
                date: pilot.date
            })
        });
        
        document.getElementById('loading').style.display = 'none';
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const creditsData = await response.json();
        
        if (creditsData && creditsData.credits !== undefined) {
            // Display credits data
            document.getElementById('pilot-display-name').textContent = creditsData.name;
            document.getElementById('credits-value').textContent = creditsData.credits.toLocaleString();
            document.getElementById('credits-display').style.display = 'block';
        } else {
            // No credits found
            document.getElementById('no-results-message').innerHTML = `${escapeHtml(i18n.noPilotData.replace('{pilot}', pilot.nick))}<br><br>${escapeHtml(i18n.noTransactions)}`;
            document.getElementById('no-results').style.display = 'block';
        }
        
    } catch (error) {
        console.error('Error searching for pilot credits:', error);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('no-results-message').innerHTML = `${escapeHtml(i18n.loadError)}<br><br>${escapeHtml(i18n.tryLater)}`;
        document.getElementById('no-results').style.display = 'block';
    }
}

// Add function to show multiple results
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
            loadPilotCredits(pilot);
        };
        resultsList.appendChild(resultItem);
    });

    document.getElementById('multiple-results').style.display = 'block';
}

function searchAgain() {
    // Reset the input
    document.getElementById('playerSearchInput').value = '';
    document.getElementById('credits-display').style.display = 'none';
    document.getElementById('no-results').style.display = 'none';
    document.getElementById('search-results').style.display = 'none';
    document.getElementById('playerSearchInput').focus();
}

// Check if there's a pilot parameter in the URL
const urlParams = new URLSearchParams(window.location.search);
const pilotParam = urlParams.get('pilot');
if (pilotParam) {
    document.getElementById('playerSearchInput').value = pilotParam;
    searchForPlayers();
}
</script>

<?php include 'footer.php'; ?>
