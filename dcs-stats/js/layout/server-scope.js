(function() {
      const storageKey = 'dcs_stats_server_scope';

      function normaliseServerName(name) {
        return String(name || '').trim();
      }

      function getSavedScope() {
        try {
          return normaliseServerName(localStorage.getItem(storageKey) || '');
        } catch (error) {
          return '';
        }
      }

      function setSavedScope(value) {
        const serverName = normaliseServerName(value);
        try {
          if (serverName) {
            localStorage.setItem(storageKey, serverName);
          } else {
            localStorage.removeItem(storageKey);
          }
        } catch (error) {
          // Some locked-down browsers can block localStorage.
        }
        window.DCS_SELECTED_SERVER = serverName;
        window.dispatchEvent(new CustomEvent('dcs-server-scope-change', { detail: { server: serverName } }));
      }

      function isServerScopeEnabled() {
        return window.DCS_SERVER_SCOPE_ENABLED !== false;
      }

      function getServerScopeFeatureKey(serverName) {
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

      function isServerScopeOptionEnabled(serverName) {
        const visibility = window.DCS_SERVER_SCOPE_CARD_VISIBILITY || {};
        return visibility[getServerScopeFeatureKey(serverName)] !== false;
      }

      window.DCS_SELECTED_SERVER = getSavedScope();
      window.getDcsSelectedServer = function() {
        if (!isServerScopeEnabled()) {
          return '';
        }
        return normaliseServerName(window.DCS_SELECTED_SERVER || getSavedScope());
      };

      async function populateServerScopeSelector() {
        const control = document.getElementById('serverScopeControl');
        const select = document.getElementById('serverScopeSelect');
        if (!control || !select || !window.dcsAPI) return;

        if (!isServerScopeEnabled()) {
          control.hidden = true;
          setSavedScope('');
          return;
        }

        try {
          const result = await window.dcsAPI.getServers({ ignoreScope: true });
          const responseData = result.data || result;
          const servers = Array.isArray(responseData) ? responseData : (responseData.servers || []);
          const names = [...new Set(servers
            .map(server => normaliseServerName(server.name || server.server_name))
            .filter(Boolean)
            .filter(isServerScopeOptionEnabled))];

          if (!names.length) {
            control.hidden = true;
            return;
          }

          const savedScope = getSavedScope();
          select.innerHTML = `<option value="">${escapeHtml(window.DCS_SERVER_SCOPE_CONFIG?.allServersLabel || 'All servers')}</option>`;
          names.forEach((name, index) => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            select.appendChild(option);
          });

          if (savedScope && names.includes(savedScope)) {
            select.value = savedScope;
            window.DCS_SELECTED_SERVER = savedScope;
          } else if (savedScope) {
            setSavedScope('');
          }

          control.hidden = false;
        } catch (error) {
          control.hidden = true;
        }
      }

      document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('serverScopeSelect');
        if (select) {
          select.addEventListener('change', function() {
            setSavedScope(select.value);
          });
        }
        populateServerScopeSelector();
      });
    })();
