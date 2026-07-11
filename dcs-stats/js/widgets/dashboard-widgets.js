(function () {
    const widgets = Array.from(document.querySelectorAll('[data-dashboard-widget]'));
    if (!widgets.length) return;
    const text = window.DCS_DASHBOARD_WIDGET_TEXT || {};
    const number = value => Number(value || 0).toLocaleString();
    const titleMap = {summary:text.summary,attendance:text.attendance,'top-pilots':text.topPilots,'combat-stats':text.combatStats,'top-squadrons':text.topSquadrons,'player-activity':text.playerActivity,'top-theatres':text.topTheatres,'top-missions':text.topMissions,'top-modules':text.topModules};

    function heading(widget) {
        const h3 = document.createElement('h3');
        h3.className = 'cms-dashboard-widget-title';
        h3.textContent = titleMap[widget.dataset.dashboardWidget] || '';
        const server = widget.dataset.serverFilter || '';
        if (server) {
            const small = document.createElement('small');
            small.textContent = server;
            h3.appendChild(small);
        }
        return h3;
    }
    function cardGrid(widget, cards) {
        const grid = document.createElement('div');
        grid.className = 'cms-widget-card-grid';
        cards.forEach(card => {
            const item = document.createElement('div');
            item.className = 'cms-widget-stat-card';
            const label = document.createElement('span');
            label.textContent = card.label;
            const value = document.createElement('strong');
            value.textContent = card.value;
            item.append(label, value);
            grid.appendChild(item);
        });
        widget.replaceChildren(heading(widget), grid);
    }
    function rankList(widget, rows) {
        if (!rows.length) return unavailable(widget, text.noData);
        const list = document.createElement('div');
        list.className = 'cms-widget-rank-list';
        rows.forEach((row, index) => {
            const item = document.createElement('div');
            item.className = 'cms-widget-rank-row';
            const rank = document.createElement('span');
            rank.textContent = String(index + 1);
            const name = document.createElement('strong');
            name.textContent = row.name;
            const value = document.createElement('em');
            value.textContent = row.value;
            item.append(rank, name, value);
            list.appendChild(item);
        });
        widget.replaceChildren(heading(widget), list);
    }
    function unavailable(widget, message) {
        const p = document.createElement('p');
        p.className = 'cms-widget-message';
        p.textContent = message || text.unavailable || 'Statistics unavailable.';
        widget.replaceChildren(heading(widget), p);
    }
    function renderStatsWidget(widget, data) {
        const type = widget.dataset.dashboardWidget;
        const attendance = data.attendance || {};
        const limit = Number(widget.dataset.limit || 5);
        if (type === 'summary') return cardGrid(widget, [
            {label:text.totalPlayers,value:number(data.totalPlayers)}, {label:text.totalPlaytime,value:number(data.totalPlaytime)},
            {label:text.averagePlaytime,value:number(Number(data.avgPlaytime || 0) / 60)}, {label:text.totalSorties,value:number(data.totalSorties)}
        ]);
        if (type === 'attendance') return cardGrid(widget, [
            {label:text.players24h,value:number(attendance.unique_players_24h)}, {label:text.players7d,value:number(attendance.unique_players_7d)},
            {label:text.players30d,value:number(attendance.unique_players_30d)}, {label:text.currentPlayers,value:number(attendance.current_players)}
        ]);
        if (type === 'combat-stats') return cardGrid(widget, [{label:text.kills,value:number(data.totalKills)},{label:text.deaths,value:number(data.totalDeaths)}]);
        if (type === 'top-theatres') return rankList(widget, (attendance.top_theatres || []).slice(0,limit).map(item => ({name:item.theatre || '-',value:`${number(item.playtime_hours)} ${text.hours}`})));
        if (type === 'top-missions') return rankList(widget, (attendance.top_missions || []).slice(0,limit).map(item => ({name:item.mission_name || '-',value:`${number(item.playtime_hours)} ${text.hours}`})));
        if (type === 'top-modules') return rankList(widget, (attendance.top_modules || []).slice(0,limit).map(item => ({name:item.module || '-',value:`${number(item.playtime_hours)} ${text.hours} · ${number(item.unique_players)} ${text.pilots}`})));
        if (type === 'player-activity') {
            const rows = (data.activityLastWeek || []).slice(-limit);
            if (!rows.length) return unavailable(widget, text.noData);
            const max = Math.max(...rows.map(row => Number(row.player_count || 0)), 1);
            const list = document.createElement('div');
            list.className = 'cms-widget-rank-list';
            rows.forEach(row => {
                const item = document.createElement('div');
                item.className = 'cms-widget-activity-row';
                const date = document.createElement('span'); date.textContent = row.date || '';
                const bar = document.createElement('span'); bar.className = 'cms-widget-activity-bar';
                const fill = document.createElement('i'); fill.style.width = `${Math.max(2,Number(row.player_count || 0) / max * 100)}%`; bar.appendChild(fill);
                const value = document.createElement('strong'); value.textContent = number(row.player_count);
                item.append(date,bar,value); list.appendChild(item);
            });
            widget.replaceChildren(heading(widget),list);
        }
    }
    async function load() {
        if (!window.dcsAPI) return widgets.forEach(widget => unavailable(widget));
        const statsTypes = new Set(['summary','attendance','combat-stats','player-activity','top-theatres','top-missions','top-modules']);
        const groups = new Map();
        widgets.filter(widget => statsTypes.has(widget.dataset.dashboardWidget)).forEach(widget => {
            const server = widget.dataset.serverFilter || '';
            if (!groups.has(server)) groups.set(server,[]);
            groups.get(server).push(widget);
        });
        await Promise.all(Array.from(groups.entries()).map(async ([server,group]) => {
            const types = new Set(group.map(widget => widget.dataset.dashboardWidget));
            try {
                const data = await window.dcsAPI.getServerStats({serverScope:server,loadServerStats:[...types].some(type => ['summary','combat-stats','player-activity'].includes(type)),loadAttendance:[...types].some(type => ['attendance','player-activity','top-theatres','top-missions','top-modules'].includes(type)),loadTopPilots:false});
                group.forEach(widget => renderStatsWidget(widget,data));
            } catch (error) { group.forEach(widget => unavailable(widget, error.message)); }
        }));
        await Promise.all(widgets.filter(widget => widget.dataset.dashboardWidget === 'top-pilots').map(async widget => {
            try {
                const metric = widget.dataset.metric || 'kills'; const limit = Number(widget.dataset.limit || 5);
                const pilots = await window.dcsAPI.getTopPilots(metric,limit,{serverScope:widget.dataset.serverFilter || ''});
                const metricLabel = metric === 'kdr_pvp' ? text.pvpKdRatio : (metric === 'kdr' ? text.kdRatio : text.kills);
                rankList(widget,pilots.slice(0,limit).map(pilot => ({name:pilot.nick || pilot.name || '-',value:`${number(pilot[metric] ?? pilot.kd_ratio)} ${metricLabel}`})));
            } catch (error) { unavailable(widget, error.message); }
        }));
        await Promise.all(widgets.filter(widget => widget.dataset.dashboardWidget === 'top-squadrons').map(async widget => {
            try {
                const limit = Number(widget.dataset.limit || 5);
                const rows = await window.dcsAPI.getTopSquadrons(limit,{serverScope:widget.dataset.serverFilter || ''});
                rankList(widget,rows.map(row => ({name:row.name || '-',value:`${number(row.credits)} ${text.credits}`})));
            } catch (error) { unavailable(widget, error.message); }
        }));
    }
    load();
}());
