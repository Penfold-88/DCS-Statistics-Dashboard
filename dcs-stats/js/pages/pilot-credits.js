const i18n = window.DCS_PILOT_CREDITS_CONFIG?.i18n || {};

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
