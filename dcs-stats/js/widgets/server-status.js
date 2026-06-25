(function () {
    const widgets = Array.from(document.querySelectorAll('[data-server-status-widget]'));
    if (!widgets.length) return;

    const text = window.DCS_SERVER_STATUS_WIDGET || {};

    function addValue(parent, label, value) {
        const item = document.createElement('div');
        const caption = document.createElement('span');
        const strong = document.createElement('strong');
        caption.textContent = label;
        strong.textContent = value;
        item.append(caption, strong);
        parent.appendChild(item);
    }

    function createCard(server, index) {
        const card = document.createElement('article');
        card.className = 'dcs-server-widget-card';
        const header = document.createElement('header');
        const name = document.createElement('h3');
        const badge = document.createElement('span');
        const status = String(server.status || text.unknown || 'Unknown');
        name.textContent = server.name || server.server_name || `${text.unknownServer || 'Server'} ${index + 1}`;
        badge.className = `dcs-server-widget-status status-${status.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`;
        badge.textContent = status;
        header.append(name, badge);

        const metrics = document.createElement('div');
        metrics.className = 'dcs-server-widget-metrics';
        const mission = server.mission || {};
        const used = Number(mission.blue_slots_used || 0) + Number(mission.red_slots_used || 0);
        const total = Number(mission.blue_slots || 0) + Number(mission.red_slots || 0);
        addValue(metrics, text.mission || 'Mission', mission.name || text.unknown || 'Unknown');
        addValue(metrics, text.theatre || 'Theatre', mission.theatre || text.unknown || 'Unknown');
        addValue(metrics, text.players || 'Players', `${used}/${total}`);
        card.append(header, metrics);
        return card;
    }

    function render(servers) {
        widgets.forEach(widget => {
            widget.replaceChildren();
            const targetServer = String(widget.dataset.serverFilter || '').trim();
            const widgetServers = targetServer
                ? servers.filter(server => String(server.name || server.server_name || '').trim() === targetServer)
                : servers;
            if (!widgetServers.length) {
                const unavailable = document.createElement('p');
                unavailable.className = 'dcs-widget-unavailable';
                unavailable.textContent = text.unavailable || 'Server status is currently unavailable.';
                widget.appendChild(unavailable);
                return;
            }
            widgetServers.forEach((server, index) => widget.appendChild(createCard(server, index)));
        });
    }

    async function load() {
        if (!window.dcsAPI || typeof window.dcsAPI.getServers !== 'function') {
            render([]);
            return;
        }
        try {
            const result = await window.dcsAPI.getServers({ignoreScope: true});
            const data = result && Object.prototype.hasOwnProperty.call(result, 'data') ? result.data : result;
            const allServers = Array.isArray(data) ? data : (data && Array.isArray(data.servers) ? data.servers : []);
            render(allServers);
        } catch (error) {
            render([]);
        }
    }

    load();
    window.addEventListener('dcs-server-scope-change', load);
    if (window.dcsAPI && typeof window.dcsAPI.getRefreshIntervalMs === 'function') {
        window.dcsAPI.getRefreshIntervalMs().then(refreshMs => window.setInterval(load, refreshMs));
    }
}());
