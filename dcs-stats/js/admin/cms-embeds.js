(function () {
    const button = document.querySelector('[data-copy-embed]');
    const code = document.querySelector('[data-embed-code]');
    const status = document.querySelector('[data-copy-status]');
    const serverSelect = document.querySelector('[data-embed-server]');
    const preview = document.querySelector('[data-embed-preview]');
    const openEmbed = document.querySelector('[data-open-embed]');
    const typeSelect = document.querySelector('[data-embed-type]');
    const metricSelect = document.querySelector('[data-embed-metric]');
    const limitSelect = document.querySelector('[data-embed-limit]');
    const metricRow = document.querySelector('[data-embed-metric-row]');
    const limitRow = document.querySelector('[data-embed-limit-row]');
    const heading = document.querySelector('[data-embed-heading]');
    const config = window.DCS_EMBED_CONFIG || {};
    if (!button || !code || !status || !serverSelect || !preview || !openEmbed || !typeSelect || !metricSelect || !limitSelect || !metricRow || !limitRow || !heading) return;

    function attribute(value) {
        return String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function updateEmbed() {
        const type = typeSelect.value;
        const isServerStatus = type === 'server-status';
        const supportsMetric = type === 'top-pilots';
        const supportsLimit = ['top-pilots', 'top-squadrons', 'player-activity', 'top-theatres', 'top-missions', 'top-modules'].includes(type);
        metricRow.hidden = !supportsMetric;
        limitRow.hidden = !supportsLimit;
        const url = new URL(isServerStatus ? config.embedUrl : config.dashboardEmbedUrl, window.location.href);
        if (!isServerStatus) url.searchParams.set('widget', type);
        if (serverSelect.value) {
            url.searchParams.set('server', serverSelect.value);
        } else {
            url.searchParams.delete('server');
        }
        if (supportsMetric) url.searchParams.set('metric', metricSelect.value);
        if (supportsLimit) url.searchParams.set('limit', limitSelect.value);
        const title = (config.types || {})[type] || 'DCS Statistics';
        const height = isServerStatus ? 360 : (['summary', 'attendance'].includes(type) ? 230 : (type === 'combat-stats' ? 190 : 390));
        preview.src = url.href;
        preview.title = title;
        preview.style.height = `${height}px`;
        openEmbed.href = url.href;
        heading.textContent = title;
        code.value = `<iframe src="${attribute(url.href)}" title="${attribute(title)}" width="100%" height="${height}" loading="lazy" style="border:0" referrerpolicy="strict-origin-when-cross-origin"></iframe>`;
        status.textContent = '';
    }

    async function loadServers() {
        try {
            const response = await fetch(config.serversEndpoint || '../get_servers.php', {credentials: 'same-origin'});
            if (!response.ok) throw new Error('Request failed');
            const result = await response.json();
            const data = result && Object.prototype.hasOwnProperty.call(result, 'data') ? result.data : result;
            const servers = Array.isArray(data) ? data : (data && Array.isArray(data.servers) ? data.servers : []);
            const names = [...new Set(servers.map(server => String(server.name || server.server_name || '').trim()).filter(Boolean))];
            names.forEach(name => {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                serverSelect.appendChild(option);
            });
        } catch (error) {
            const option = document.createElement('option');
            option.disabled = true;
            option.textContent = config.serversUnavailable || 'Servers unavailable';
            serverSelect.appendChild(option);
        }
    }

    serverSelect.addEventListener('change', updateEmbed);
    typeSelect.addEventListener('change', updateEmbed);
    metricSelect.addEventListener('change', updateEmbed);
    limitSelect.addEventListener('change', updateEmbed);

    button.addEventListener('click', async () => {
        let copied = false;
        try {
            await navigator.clipboard.writeText(code.value);
            copied = true;
        } catch (error) {
            code.focus();
            code.select();
            copied = document.execCommand('copy');
        }
        status.textContent = copied ? (config.copied || 'Embed code copied.') : '';
    });
    loadServers();
}());
