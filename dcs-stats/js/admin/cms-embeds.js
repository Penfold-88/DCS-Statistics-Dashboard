(function () {
    const button = document.querySelector('[data-copy-embed]');
    const code = document.querySelector('[data-embed-code]');
    const status = document.querySelector('[data-copy-status]');
    const serverSelect = document.querySelector('[data-embed-server]');
    const preview = document.querySelector('[data-embed-preview]');
    const openEmbed = document.querySelector('[data-open-embed]');
    const config = window.DCS_EMBED_CONFIG || {};
    if (!button || !code || !status || !serverSelect || !preview || !openEmbed) return;

    function attribute(value) {
        return String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function updateEmbed() {
        const url = new URL(config.embedUrl, window.location.href);
        if (serverSelect.value) {
            url.searchParams.set('server', serverSelect.value);
        } else {
            url.searchParams.delete('server');
        }
        preview.src = url.href;
        openEmbed.href = url.href;
        code.value = `<iframe src="${attribute(url.href)}" title="${attribute(config.title || 'Server Status')}" width="100%" height="360" loading="lazy" style="border:0" referrerpolicy="strict-origin-when-cross-origin"></iframe>`;
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
