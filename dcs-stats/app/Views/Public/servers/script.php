<script>
const serverCardVisibility = <?php echo json_encode($serverCardVisibility); ?>;
const maskExtensionSecrets = <?php echo json_encode(isFeatureEnabled('server_detail_mask_extension_secrets')); ?>;
const publicDateFormat = <?php echo json_encode(dcs_public_date_format()); ?>;
const i18n = <?php echo json_encode([
    'unknownServer' => dcs_t('servers.unknown_server'),
    'unknown' => dcs_t('servers.unknown'),
    'notAvailable' => dcs_t('servers.not_available'),
    'noWeather' => dcs_t('servers.no_weather'),
    'noExtensions' => dcs_t('servers.no_extensions'),
    'noPlayers' => dcs_t('servers.no_players'),
    'extension' => dcs_t('servers.extension'),
    'wind' => dcs_t('servers.wind'),
    'degrees' => dcs_t('servers.degrees'),
    'cloudBase' => dcs_t('servers.cloud_base'),
    'slotsUsed' => dcs_t('servers.slots_used'),
    'blueShort' => dcs_t('servers.blue_short'),
    'redShort' => dcs_t('servers.red_short'),
    'mission' => dcs_t('servers.mission'),
    'theatre' => dcs_t('servers.theatre'),
    'slots' => dcs_t('servers.slots'),
    'restart' => dcs_t('servers.restart'),
    'weather' => dcs_t('servers.weather'),
    'extensions' => dcs_t('servers.extensions'),
    'activePlayers' => dcs_t('servers.active_players')
], JSON_UNESCAPED_UNICODE); ?>;

function getServerCardFeatureKey(serverName) {
    let slug = String(serverName || 'unknown_server')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');

    if (!slug) {
        slug = 'unknown_server';
    }

    return `server_card_${slug}`;
}

function isServerCardEnabled(serverName) {
    const featureKey = getServerCardFeatureKey(serverName);
    return serverCardVisibility[featureKey] !== false;
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

function formatPublicDateTime(value) {
    if (!value) {
        return i18n.notAvailable;
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return `${formatPublicDate(date)} ${date.toLocaleTimeString()}`;
}

async function loadServers() {
    try {
        // Use client-side API
        const result = await window.dcsAPI.getServers();
        
        document.getElementById('servers-loading').style.display = 'none';
        
        // Handle both direct data and wrapped response
        const data = result.data || result;
        
        if (!data || result.error || data.length === 0) {
            document.getElementById('no-servers').style.display = 'block';
            return;
        }
        
        const serverDetailsGrid = document.getElementById('serverDetailsGrid');
        if (!serverDetailsGrid) {
            document.getElementById('no-servers').style.display = 'block';
            return;
        }

        serverDetailsGrid.innerHTML = '';
        
        // Handle both array and object with servers property
        const selectedServer = window.getDcsSelectedServer ? window.getDcsSelectedServer() : '';
        const allServers = Array.isArray(data) ? data : (data.servers || []);
        const servers = selectedServer
            ? allServers.filter(server => String(server.name || server.server_name || '').trim() === selectedServer)
            : allServers;
        let visibleServerCount = 0;
        
        servers.forEach((server, index) => {
            const serverDisplayName = server.name || server.server_name || `Server ${index + 1}`;
            if (!isServerCardEnabled(serverDisplayName)) {
                return;
            }

            visibleServerCount++;

            // Extract mission data if available
            let missionName = i18n.notAvailable;
            let theatre = i18n.notAvailable;
            let playerCount = i18n.notAvailable;
            let uptime = i18n.notAvailable;
            let slotSummary = i18n.notAvailable;
            let activeSlotCount = 0;
            
            if (server.mission) {
                missionName = server.mission.name || i18n.notAvailable;
                theatre = server.mission.theatre || i18n.notAvailable;
                
                // Calculate player counts
                const blueUsed = server.mission.blue_slots_used || 0;
                const blueTotal = server.mission.blue_slots || 0;
                const redUsed = server.mission.red_slots_used || 0;
                const redTotal = server.mission.red_slots || 0;
                const totalUsed = blueUsed + redUsed;
                const totalSlots = blueTotal + redTotal;
                activeSlotCount = totalUsed;
                
                playerCount = `${totalUsed}/${totalSlots} (${i18n.blueShort}:${blueUsed}/${blueTotal} ${i18n.redShort}:${redUsed}/${redTotal})`;
                slotSummary = `${totalUsed}/${totalSlots} ${i18n.slotsUsed}`;
                
                // Format uptime
                if (server.mission.uptime !== undefined) {
                    const hours = Math.floor(server.mission.uptime / 3600);
                    const minutes = Math.floor((server.mission.uptime % 3600) / 60);
                    uptime = `${hours}h ${minutes}m`;
                }
            }
            
            serverDetailsGrid.appendChild(createServerDetailCard({ ...server, name: serverDisplayName }, {
                missionName,
                theatre,
                playerCount,
                uptime,
                slotSummary,
                activeSlotCount
            }));
        });

        if (visibleServerCount === 0) {
            document.getElementById('no-servers').style.display = 'block';
            document.getElementById('servers-container').style.display = 'none';
            return;
        }
        
        document.getElementById('servers-container').style.display = 'block';
        
    } catch (error) {
        // Error loading servers
        document.getElementById('servers-loading').style.display = 'none';
        document.getElementById('no-servers').style.display = 'block';
    }
}

function formatWeather(weather) {
    if (!weather) return i18n.noWeather;
    const parts = [];
    if (weather.temperature !== undefined && weather.temperature !== null) parts.push(`${weather.temperature}C`);
    if (weather.wind_speed !== undefined && weather.wind_speed !== null) parts.push(`${Number(weather.wind_speed).toFixed(1)} m/s ${i18n.wind}`);
    if (weather.wind_direction !== undefined && weather.wind_direction !== null) parts.push(`${weather.wind_direction} ${i18n.degrees}`);
    if (weather.clouds_base !== undefined && weather.clouds_base !== null) parts.push(`${i18n.cloudBase} ${weather.clouds_base}m`);
    return parts.length ? parts.join(' | ') : i18n.noWeather;
}

function maskSecretText(value) {
    const text = String(value || '');
    if (!maskExtensionSecrets || text === '') {
        return text;
    }

    return text
        .split(/\r?\n/)
        .map(line => line.replace(/(\b(?:pass|password|pwd|token|secret|api key|apikey|key)\b\s*[:=]\s*)([^\s|,;]+)/ig, '$1*****'))
        .join('\n');
}

function formatExtensions(extensions) {
    if (!Array.isArray(extensions) || extensions.length === 0) return `<span class="muted">${escapeHtml(i18n.noExtensions)}</span>`;
    return extensions.map(ext => `
        <div class="detail-list-item">
            <strong>${escapeHtml(ext.name || i18n.extension)}</strong>
            <span>${escapeHtml(ext.version || '')}</span>
            <small>${escapeHtml(maskSecretText(ext.value || ''))}</small>
        </div>
    `).join('');
}

function formatPlayers(players) {
    if (!Array.isArray(players) || players.length === 0) return `<span class="muted">${escapeHtml(i18n.noPlayers)}</span>`;
    return players.slice(0, 8).map(player => `
        <div class="detail-list-item compact">
            <strong>${escapeHtml(player.name || player.nick || i18n.unknown)}</strong>
            <span>${escapeHtml(player.side || player.coalition || '')}</span>
        </div>
    `).join('');
}

function getCoalitionPlayerCount(server, keys) {
    if (!server.mission) return 0;

    return keys.reduce((total, key) => total + Number(server.mission[key] || 0), 0);
}

function takePlayersBySide(players, side, count) {
    if (count <= 0) return [];

    return players
        .filter(player => String(player.side || player.coalition || '').toUpperCase() === side && hasActivePlayerUnit(player))
        .slice(-count);
}

function hasActivePlayerUnit(player) {
    const unitType = String(player.unit_type || player.unit || player.aircraft || '').trim();
    const inactiveUnits = ['observer', 'spectator', 'cvn_71'];
    const activeUnit = unitType && !inactiveUnits.includes(unitType.toLowerCase());
    const callsign = String(player.callsign || '').trim();

    return activeUnit || callsign !== '';
}

function getLiveActivePlayersForDisplay(server, summary) {
    const status = String(server.status || '').toLowerCase();
    const inactiveStatuses = ['offline', 'paused', 'shutdown', 'stopped', 'not running'];
    const activeSlotCount = Number(summary.activeSlotCount || 0);

    if (inactiveStatuses.includes(status) || activeSlotCount === 0) {
        return [];
    }

    const players = Array.isArray(server.players) ? server.players : [];
    const activePlayers = players.filter(hasActivePlayerUnit);
    if (activePlayers.length > 0 && activePlayers.length <= activeSlotCount) {
        return activePlayers;
    }

    const blueCount = getCoalitionPlayerCount(server, ['blue_slots_used', 'blue_players', 'blue_count']);
    const redCount = getCoalitionPlayerCount(server, ['red_slots_used', 'red_players', 'red_count']);
    const coalitionPlayers = [
        ...takePlayersBySide(players, 'BLUE', blueCount),
        ...takePlayersBySide(players, 'RED', redCount)
    ];

    if (coalitionPlayers.length > 0) {
        return coalitionPlayers.slice(0, activeSlotCount);
    }

    return activePlayers.slice(-activeSlotCount);
}

function createServerDetailCard(server, summary) {
    const card = document.createElement('article');
    card.className = 'server-detail-card';

    const weather = formatWeather(server.weather);
    const extensions = formatExtensions(server.extensions);
    const players = formatPlayers(getLiveActivePlayersForDisplay(server, summary));
    const restart = formatPublicDateTime(server.restart_time);
    const status = server.status || i18n.unknown;
    const statusClass = `detail-status status-${String(status).toLowerCase()}`;

    card.innerHTML = `
        <div class="server-detail-header">
            <div>
                <h3>${escapeHtml(server.name || i18n.unknownServer)}</h3>
                <?php if (isFeatureEnabled('server_detail_description')): ?>
                <p class="server-description">${escapeHtml(server.description || '')}</p>
                <?php endif; ?>
            </div>
            <?php if (isFeatureEnabled('server_detail_status')): ?>
            <span class="${statusClass}">${escapeHtml(status)}</span>
            <?php endif; ?>
        </div>
        <?php if (isFeatureEnabled('server_detail_mission') || isFeatureEnabled('server_detail_slots') || isFeatureEnabled('server_detail_restart')): ?>
        <div class="detail-metrics">
            <?php if (isFeatureEnabled('server_detail_mission')): ?>
            <div class="mission-metric"><span>${escapeHtml(i18n.mission)}</span><strong>${escapeHtml(summary.missionName)}</strong></div>
            <div><span>${escapeHtml(i18n.theatre)}</span><strong>${escapeHtml(summary.theatre)}</strong></div>
            <?php endif; ?>
            <?php if (isFeatureEnabled('server_detail_slots')): ?>
            <div><span>${escapeHtml(i18n.slots)}</span><strong>${escapeHtml(summary.slotSummary)}</strong></div>
            <?php endif; ?>
            <?php if (isFeatureEnabled('server_detail_restart')): ?>
            <div><span>${escapeHtml(i18n.restart)}</span><strong>${escapeHtml(restart)}</strong></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (isFeatureEnabled('server_detail_weather') || isFeatureEnabled('server_detail_extensions') || isFeatureEnabled('server_detail_active_players')): ?>
        <div class="detail-split">
            <?php if (isFeatureEnabled('server_detail_weather')): ?>
            <section>
                <h4>${escapeHtml(i18n.weather)}</h4>
                <p>${escapeHtml(weather)}</p>
            </section>
            <?php endif; ?>
            <?php if (isFeatureEnabled('server_detail_extensions')): ?>
            <section>
                <h4>${escapeHtml(i18n.extensions)}</h4>
                ${extensions}
            </section>
            <?php endif; ?>
            <?php if (isFeatureEnabled('server_detail_active_players')): ?>
            <section>
                <h4>${escapeHtml(i18n.activePlayers)}</h4>
                ${players}
            </section>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    `;

    return card;
}

// Load servers on page load and refresh using the configured API interval
document.addEventListener('DOMContentLoaded', async () => {
    loadServers();
    window.addEventListener('dcs-server-scope-change', loadServers);
    const refreshMs = window.dcsAPI ? await window.dcsAPI.getRefreshIntervalMs() : 600000;
    setInterval(loadServers, refreshMs);
});
</script>