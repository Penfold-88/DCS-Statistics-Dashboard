    const customLinksConfig = window.DCS_ADMIN_CUSTOM_LINKS_CONFIG || {};
    let customLinkIndex = Number(customLinksConfig.nextIndex || 1);
    const customLinksText = customLinksConfig.i18n || {};

    function addCustomLink() {
        const editor = document.getElementById('customLinksEditor');
        const row = document.createElement('div');
        row.className = 'custom-link-row';
        row.innerHTML = `
            <div class="form-group">
                <label>${customLinksText.label}</label>
                <input type="text" name="custom_links[${customLinkIndex}][label]" class="form-control" placeholder="Tacview">
            </div>
            <div class="form-group">
                <label>${customLinksText.url}</label>
                <input type="text" name="custom_links[${customLinkIndex}][url]" class="form-control" placeholder="https://example.com">
            </div>
            <label class="custom-link-check">
                <input type="checkbox" name="custom_links[${customLinkIndex}][enabled]" value="1" checked>
                ${customLinksText.enabled}
            </label>
            <label class="custom-link-check">
                <input type="checkbox" name="custom_links[${customLinkIndex}][new_tab]" value="1" checked>
                ${customLinksText.newTab}
            </label>
            <button type="button" class="btn btn-danger btn-small" onclick="removeCustomLink(this)">${customLinksText.remove}</button>
        `;
        editor.appendChild(row);
        customLinkIndex++;
    }

    function removeCustomLink(button) {
        const row = button.closest('.custom-link-row');
        const editor = document.getElementById('customLinksEditor');

        if (row) {
            row.remove();
        }

        if (editor && editor.children.length === 0) {
            addCustomLink();
        }
    }
