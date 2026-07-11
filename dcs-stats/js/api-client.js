/**
 * DCS Stats API Client
 * Handles all API calls from the browser, bypassing PHP restrictions
 */

class DCSStatsAPI {
    constructor() {
        this.config = null;
        this.configLoaded = false;
        this.basePath = window.DCS_CONFIG ? window.DCS_CONFIG.basePath : '';
    }

    // Helper to build proper URLs
    buildUrl(path) {
        if (!path) return this.basePath;
        const cleanPath = path.replace(/^\//, '');
        return this.basePath ? `${this.basePath}/${cleanPath}` : `/${cleanPath}`;
    }

    async loadConfig() {
        if (this.configLoaded) return this.config;
        if (this.configPromise) return this.configPromise;
        
        this.configPromise = (async () => {
            const response = await fetch(this.buildUrl('get_api_config.php'));
            this.config = await response.json();
            this.configLoaded = true;
            return this.config;
        })();

        try {
            return await this.configPromise;
        } catch (error) {
            // Failed to load API config
            this.config = { use_api: true };
            this.configLoaded = true;
            return this.config;
        } finally {
            this.configPromise = null;
        }
    }

    async makeAPICall(endpoint, options = {}) {
        const config = await this.loadConfig();
        const unavailableMessage = this.getUnavailableMessage(config);
        
        if (!config.use_api) {
            // API not enabled or no base URL configured
            throw new Error(unavailableMessage);
        }

        // First try proxy endpoint
        const method = options.method || 'GET';
        endpoint = this.prepareScopedEndpoint(endpoint, options);
        const proxyUrl = this.buildUrl(`api_proxy.php?endpoint=${encodeURIComponent(endpoint)}&method=${method}`);
        
        // Making API call via proxy

        const timeout = Math.min(Math.max(Number(config.timeout || 30), 5), 60) * 1000;
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);

        try {
            const fetchOptions = {
                signal: controller.signal,
                headers: {
                    'Accept': 'application/json'
                }
            };

            // For POST requests, send data as JSON in body
            if (method === 'POST' && (options.body || options.data)) {
                const data = this.prepareScopedData(options.body || options.data || {}, options);
                
                // If body is FormData, convert to object
                if (data instanceof FormData) {
                    const formObject = {};
                    for (let [key, value] of data.entries()) {
                        formObject[key] = value;
                    }
                    fetchOptions.body = JSON.stringify(formObject);
                } else {
                    fetchOptions.body = JSON.stringify(data);
                }
                
                fetchOptions.method = 'POST';
                fetchOptions.headers['Content-Type'] = 'application/json';
            }

            const response = await fetch(proxyUrl, fetchOptions);
            clearTimeout(timeoutId);

            if (!response.ok) {
                // API call failed
                throw new Error(unavailableMessage);
            }

            const data = await response.json();
            if (data && data.error) {
                throw new Error(unavailableMessage);
            }
            // API response received
            return data;
        } catch (error) {
            clearTimeout(timeoutId);
            // API proxy call error
            
            throw new Error(unavailableMessage);
        }
    }

    getUnavailableMessage(config = this.config || {}) {
        const message = String(config.api_unavailable_message || '').trim();
        return message || 'API Currently Unavailable';
    }

    async request(endpoint, options = {}) {
        return this.makeAPICall(endpoint, options);
    }

    getSelectedServerScope(options = {}) {
        if (options.ignoreScope) return '';
        if (options.serverScope !== undefined) return String(options.serverScope || '').trim();
        if (typeof window.getDcsSelectedServer === 'function') {
            return String(window.getDcsSelectedServer() || '').trim();
        }
        return String(window.DCS_SELECTED_SERVER || '').trim();
    }

    prepareScopedEndpoint(endpoint, options = {}) {
        const method = options.method || 'GET';
        const serverScope = this.getSelectedServerScope(options);
        if (!serverScope || method !== 'GET') {
            return endpoint;
        }

        const existingQuery = endpoint.includes('?') ? endpoint.split('?').slice(1).join('?') : '';
        const existingParams = new URLSearchParams(existingQuery);
        if (existingParams.has('server') || existingParams.has('server_name')) {
            return endpoint;
        }

        const separator = endpoint.includes('?') ? '&' : '?';
        const params = new URLSearchParams({
            server: serverScope,
            server_name: serverScope
        });
        return `${endpoint}${separator}${params.toString()}`;
    }

    prepareScopedData(data, options = {}) {
        const serverScope = this.getSelectedServerScope(options);
        if (!serverScope) {
            return data;
        }

        if (data instanceof FormData) {
            if (!data.has('server')) data.append('server', serverScope);
            if (!data.has('server_name')) data.append('server_name', serverScope);
            return data;
        }

        const scopedData = { ...(data || {}) };
        if (!scopedData.server) scopedData.server = serverScope;
        if (!scopedData.server_name) scopedData.server_name = serverScope;
        return scopedData;
    }

    getCacheKey(name, options = {}) {
        const serverScope = this.getSelectedServerScope(options) || 'all';
        return `dcs_stats_cache_${name}_${serverScope}`;
    }

    getCachedValue(name, maxAgeMs, options = {}) {
        try {
            const raw = localStorage.getItem(this.getCacheKey(name, options));
            if (!raw) return null;

            const cached = JSON.parse(raw);
            if (!cached || !cached.savedAt || !Object.prototype.hasOwnProperty.call(cached, 'data')) {
                return null;
            }

            if (Date.now() - Number(cached.savedAt) > maxAgeMs) {
                localStorage.removeItem(this.getCacheKey(name, options));
                return null;
            }

            return cached.data;
        } catch (error) {
            return null;
        }
    }

    setCachedValue(name, data, options = {}) {
        try {
            localStorage.setItem(this.getCacheKey(name, options), JSON.stringify({
                savedAt: Date.now(),
                data
            }));
        } catch (error) {
            // Some locked-down browsers can block localStorage.
        }
    }

    async getServerAttendance(options = {}) {
        const cacheName = 'server_attendance';
        const cacheTtlMs = 15 * 60 * 1000;
        const cached = this.getCachedValue(cacheName, cacheTtlMs, options);
        if (cached) {
            return cached;
        }

        const attendance = await this.makeAPICall('/server_attendance', options);
        this.setCachedValue(cacheName, attendance, options);
        return attendance;
    }

    async getSquadrons(options = {}) {
        const cacheName = 'squadrons';
        const cacheTtlMs = 15 * 60 * 1000;
        const cached = this.getCachedValue(cacheName, cacheTtlMs, options);
        if (cached) {
            return cached;
        }

        const squadrons = await this.makeAPICall('/squadrons', options);
        const safeSquadrons = Array.isArray(squadrons) ? squadrons : [];
        this.setCachedValue(cacheName, safeSquadrons, options);
        return safeSquadrons;
    }

    async getSquadronCredits(name, options = {}) {
        const cacheName = `squadron_credits_${String(name || '').toLowerCase()}`;
        const cacheTtlMs = 15 * 60 * 1000;
        const cached = this.getCachedValue(cacheName, cacheTtlMs, options);
        if (cached) {
            return cached;
        }

        const credits = await this.makeAPICall('/squadron_credits', {
            ...options,
            method: 'POST',
            data: { name }
        });
        const safeCredits = credits || {};
        this.setCachedValue(cacheName, safeCredits, options);
        return safeCredits;
    }

    async getPilotTraps(playerName, playerDate = null, options = {}) {
        const cleanName = String(playerName || '').trim();
        if (!cleanName) {
            return [];
        }

        const cacheName = `pilot_traps_${cleanName.toLowerCase()}_${playerDate || 'latest'}_${options.limit || 10}_${options.offset || 0}`;
        const cacheTtlMs = 15 * 60 * 1000;
        const cached = this.getCachedValue(cacheName, cacheTtlMs, options);
        if (cached !== null) {
            return cached;
        }

        const requestData = {
            nick: cleanName,
            limit: options.limit || 10,
            offset: options.offset || 0
        };

        if (playerDate) {
            requestData.date = playerDate;
        }

        const response = await this.makeAPICall('/traps', {
            ...options,
            method: 'POST',
            data: requestData
        });

        const traps = Array.isArray(response)
            ? response
            : (response?.data || response?.traps || response?.rows || []);
        const safeTraps = Array.isArray(traps) ? traps : [];
        this.setCachedValue(cacheName, safeTraps, options);
        return safeTraps;
    }

    async getRefreshIntervalMs() {
        const config = await this.loadConfig();
        const seconds = Number(config.refresh_interval || 300);
        return Math.max(seconds, 60) * 1000;
    }

    async getLeaderboard(options = {}) {
        const config = await this.loadConfig();
        
        if (!config.use_api) {
            throw new Error(this.getUnavailableMessage(config));
        }

        const leaderboard = await this.makeAPICall('/leaderboard?what=kills&limit=10');
        const items = Array.isArray(leaderboard) ? leaderboard : (leaderboard.items || []);
        const needsPlayerDetails = options.loadPlayerDetails !== false;
        const detailedPlayers = await Promise.all(items.map(async (player, index) => {
            let overall = {};
            let mostUsedAircraft = null;

            if (needsPlayerDetails) {
                try {
                    const playerInfo = await this.makeAPICall('/player_info', {
                        method: 'POST',
                        data: { nick: player.nick }
                    });
                    overall = playerInfo.overall || {};
                    const moduleKills = Array.isArray(overall.killsByModule) ? overall.killsByModule : [];
                    mostUsedAircraft = moduleKills.length ? moduleKills[0].module : null;
                } catch (error) {
                    overall = {};
                }
            }

            return {
                rank: player.row_num || index + 1,
                nick: player.nick,
                name: player.nick,
                date: player.date,
                kills: player.kills ?? overall.kills ?? 0,
                deaths: player.deaths ?? overall.deaths ?? 0,
                kd_ratio: Number(player.kdr ?? overall.kdr ?? 0),
                kdr: Number(player.kdr ?? overall.kdr ?? 0),
                kills_pvp: player.kills_pvp ?? overall.kills_pvp ?? 0,
                deaths_pvp: player.deaths_pvp ?? overall.deaths_pvp ?? 0,
                kdr_pvp: Number(player.kdr_pvp ?? overall.kdr_pvp ?? 0),
                credits: player.credits ?? 0,
                playtime: player.playtime ?? overall.playtime ?? 0,
                sorties: overall.sorties ?? null,
                takeoffs: overall.takeoffs ?? null,
                landings: overall.landings ?? null,
                crashes: overall.crashes ?? null,
                ejections: overall.ejections ?? null,
                most_used_aircraft: mostUsedAircraft
            };
        }));
        
        return {
            data: detailedPlayers,
            source: 'api-client',
            count: detailedPlayers.length,
            total_count: leaderboard.total_count || detailedPlayers.length,
            generated: new Date().toISOString()
        };
    }

    async getTopPilots(metric = 'kills', limit = 5, options = {}) {
        const config = await this.loadConfig();

        if (!config.use_api) {
            throw new Error(this.getUnavailableMessage(config));
        }

        const metricMap = {
            kills: 'kills',
            kdr: 'kdr',
            kdr_pvp: 'kdr_pvp'
        };
        const what = metricMap[metric] || 'kills';
        const normalize = (player, index) => ({
            rank: player.row_num || index + 1,
            nick: player.nick,
            name: player.nick,
            date: player.date,
            kills: Number(player.kills || 0),
            deaths: Number(player.deaths || 0),
            kd_ratio: Number(player.kd_ratio ?? player.kdr ?? 0),
            kdr: Number(player.kdr ?? player.kd_ratio ?? 0),
            kills_pvp: Number(player.kills_pvp || 0),
            deaths_pvp: Number(player.deaths_pvp || 0),
            kdr_pvp: Number(player.kdr_pvp || 0),
            credits: Number(player.credits || 0),
            playtime: Number(player.playtime || 0)
        });
        const valueForMetric = (player) => {
            if (what === 'kdr') return Number(player.kdr ?? player.kd_ratio ?? 0);
            if (what === 'kdr_pvp') return Number(player.kdr_pvp || 0);
            return Number(player.kills || 0);
        };

        try {
            const leaderboard = await this.makeAPICall(`/leaderboard?what=${encodeURIComponent(what)}&limit=${Number(limit) || 5}`, options);
            const items = Array.isArray(leaderboard) ? leaderboard : (leaderboard.items || []);
            return items.map(normalize);
        } catch (error) {
            if (what === 'kills') throw error;
            const fallback = await this.makeAPICall('/leaderboard?what=kills&limit=100', options);
            const items = Array.isArray(fallback) ? fallback : (fallback.items || []);
            return items.map(normalize)
                .sort((a, b) => valueForMetric(b) - valueForMetric(a))
                .slice(0, Number(limit) || 5);
        }
    }

    async getTopSquadrons(limit = 3, options = {}) {
        const squadrons = await this.getSquadrons(options);
        const safeLimit = Math.max(1, Number(limit) || 3);
        const candidates = squadrons.slice(0, Math.max(safeLimit, 10));

        const squadronsWithCredits = await Promise.all(
            candidates.map(async (squadron) => {
                try {
                    const credits = await this.getSquadronCredits(squadron.name, options);
                    return {
                        name: squadron.name,
                        credits: Number(credits.credits || 0)
                    };
                } catch (error) {
                    return {
                        name: squadron.name,
                        credits: 0
                    };
                }
            })
        );

        return squadronsWithCredits
            .sort((a, b) => b.credits - a.credits)
            .slice(0, safeLimit);
    }

    async getServerStats(options = {}) {
        const config = await this.loadConfig();
        
        if (!config.use_api) {
            throw new Error(this.getUnavailableMessage(config));
        }

        const [stats, attendance, topkills] = await Promise.all([
            options.loadServerStats !== false
                ? this.makeAPICall('/serverstats', { ...options, data: {} })
                : Promise.resolve({}),
            options.loadAttendance !== false
                ? this.getServerAttendance(options)
                : Promise.resolve({}),
            options.loadTopPilots !== false
                ? this.getTopPilots('kills', 5, options)
                : Promise.resolve([])
        ]);

        return {
            totalPlayers: stats.totalPlayers || 0,
            totalPlaytime: stats.totalPlaytime || 0,
            avgPlaytime: stats.avgPlaytime || 0,
            activePlayers: stats.activePlayers || attendance.current_players || 0,
            totalSorties: stats.totalSorties || 0,
            totalKills: stats.totalKills || 0,
            totalDeaths: stats.totalDeaths || 0,
            totalPvPKills: stats.totalPvPKills || 0,
            totalPvPDeaths: stats.totalPvPDeaths || 0,
            top5Pilots: topkills,
            top3Squadrons: [],
            activityLastWeek: stats.daily_players || attendance.daily_trend || [],
            attendance: attendance
        };
    }

    async searchPlayers(searchTerm) {
        const config = await this.loadConfig();
        
        if (!config.use_api) {
            throw new Error(this.getUnavailableMessage(config));
        }

        const players = await this.makeAPICall('/getuser', {
            method: "POST",
            data: { nick: searchTerm }
        });

        if (players && players.length > 0) {
            // Found exact or partial matches via getuser
            return {
                count: players.length,
                results: players.map(p => ({
                    nick: p.nick,
                    date: p.date
                }))
            };
        }
    }
    
    // Simple fuzzy matching for common typos
    fuzzyMatch(str1, str2) {
        // Check if strings are very similar (1-2 character difference)
        if (Math.abs(str1.length - str2.length) > 2) return false;
        
        let differences = 0;
        const longer = str1.length > str2.length ? str1 : str2;
        const shorter = str1.length > str2.length ? str2 : str1;
        
        for (let i = 0; i < shorter.length; i++) {
            if (longer[i] !== shorter[i]) differences++;
            if (differences > 2) return false;
        }
        
        return differences <= 2;
    }

    async getPlayerStats(playerName, playerDate = null) {
        const config = await this.loadConfig();
        
        if (!config.use_api) {
            throw new Error(this.getUnavailableMessage(config));
        }

        const users = await this.makeAPICall('/getuser', {
            method: "POST",
            data: { nick: playerName }
        });

        if (users && users.length > 0) {
            const user = users[0];

            const info = await this.makeAPICall('/player_info', {
                method: 'POST',
                data: {
                    nick: user.nick,
                    date: playerDate || user.date
                }
            });
            const stats = info.overall || {};
            const lastSession = info.last_session || {};
            const moduleStats = info.module_stats || stats.killsByModule || [];

            // Check if stats is empty object
            if (!stats || Object.keys(stats).length === 0) {
                throw new Error(`No statistics found for player "${user.nick}". They may not have any recorded combat data.`);
            }
            
            // Find most used aircraft from kills_by_module
            let mostUsedAircraft = 'N/A';
            // API returns killsByModule as array format
            if (stats.killsByModule && Array.isArray(stats.killsByModule)) {
                if (stats.killsByModule.length > 0) {
                    // Sort by kills and get the module with most kills
                    const sorted = [...stats.killsByModule].sort((a, b) => b.kills - a.kills);
                    mostUsedAircraft = sorted[0].module;
                }
            }

            if (Array.isArray(moduleStats) && moduleStats.length > 0) {
                const sorted = [...moduleStats].sort((a, b) => (b.kills || 0) - (a.kills || 0));
                mostUsedAircraft = sorted[0].module || mostUsedAircraft;
            }
            
            return {
                source: 'api-client',
                data: {
                    nick: user.nick,
                    kills: stats.kills || 0,
                    deaths: stats.deaths || 0,
                    kdr: stats.kdr || 0,
                    kd_ratio: Number(stats.kdr || 0),
                    kills_pvp: stats.kills_pvp || 0,
                    deaths_pvp: stats.deaths_pvp || 0,
                    kdr_pvp: stats.kdr_pvp || 0,
                    kills_by_module: moduleStats && Array.isArray(moduleStats) ?
                        moduleStats.reduce((acc, item) => {
                            acc[item.module] = item.kills;
                            return acc;
                        }, {}) : 
                        (stats.killsByModule || {}),
                    last_session_kills: lastSession.kills || stats.lastSessionKills || 0,
                    last_session_deaths: lastSession.deaths || stats.lastSessionDeaths || 0,
                    takeoffs: stats.takeoffs || 0,
                    landings: stats.landings || 0,
                    crashes: stats.crashes || 0,
                    ejections: stats.ejections || 0,
                    sorties: stats.sorties || 0,
                    playtime: stats.playtime || 0,
                    current_server: info.current_server || null,
                    credits: info.credits ? (info.credits.credits || 0) : 0,
                    rank: info.credits ? info.credits.rank : null,
                    campaign: info.credits ? info.credits.name : null,
                    squadrons: info.squadrons || [],
                    squadron: info.squadrons && info.squadrons.length ? info.squadrons[0].name : null,
                    carrier_traps: stats.carrier_traps || stats.carrierTraps || 0,
                    avgTrapScore: stats.avgTrapScore || stats.avg_trap_score || 0,
                    trapScores: stats.trapScores || [],
                    most_used_aircraft: mostUsedAircraft,
                    aircraftUsage: Array.isArray(moduleStats) ? moduleStats.map(item => ({
                        name: item.module,
                        count: item.kills || 0
                    })) : (stats.aircraftUsage || [])
                }
            };
        }
        
        // If no users found, throw more informative error
        if (!users || users.length === 0) {
            throw new Error(`No player found with name "${playerName}"`);
        }
        
        throw new Error('Failed to get player stats from API');
    }

    async getCredits() {
        const config = await this.loadConfig();
        
        if (!config.use_api) {
            throw new Error(this.getUnavailableMessage(config));
        }

        const leaderboard = await this.makeAPICall('/leaderboard?what=credits&limit=100');
        const credits = leaderboard.items || [];
        
        // Transform to expected format
        return credits.map(player => ({
            name: player.nick,
            nick: player.nick,
            credits: player.credits || 0,
            kills: player.kills || 0,
            deaths: player.deaths || 0,
            kdr: player.kdr || 0
        })).sort((a, b) => b.credits - a.credits);
    }

    async getServers(options = {}) {
        const config = await this.loadConfig();
        
        if (!config.use_api) {
            throw new Error(this.getUnavailableMessage(config));
        }

        // Use new /servers endpoint
        const data = await this.makeAPICall('/servers', { ignoreScope: options.ignoreScope === true });
        return {
            data: data,
            source: 'api-client',
            generated: new Date().toISOString()
        };
    }

    async getSquadronData(type) {
        // Squadron data is now available via API
        const endpoints = {
            'squadrons': '/get_squadrons.php',
            'squadron_members': '/get_squadron_members.php',
            'squadron_credits': '/get_squadron_credits.php'
        };
        
        const endpoint = endpoints[type];
        if (!endpoint) {
            throw new Error(`Unknown squadron data type: ${type}`);
        }
        
        return this.request(endpoint);
    }
}

// Create global instance
window.dcsAPI = new DCSStatsAPI();
