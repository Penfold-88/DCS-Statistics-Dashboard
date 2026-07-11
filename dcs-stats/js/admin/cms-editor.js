(function () {
    const wrapper = document.querySelector('[data-cms-editor]');
    const form = document.getElementById('cms-page-form');
    if (!wrapper || !form) return;

    const editor = wrapper.querySelector('.cms-editor-content');
    const textarea = document.getElementById('cms_content');
    const format = wrapper.querySelector('[data-editor-format]');
    const text = window.DCS_CMS_EDITOR_TEXT || {};
    const mediaConfig = window.DCS_CMS_MEDIA || {};
    const widgetConfig = window.DCS_CMS_WIDGET_CONFIG || {};
    let mediaItems = Array.isArray(mediaConfig.items) ? mediaConfig.items : [];
    let savedRange = null;

    function focusEditor() {
        editor.focus();
    }

    function run(command, value) {
        focusEditor();
        document.execCommand(command, false, value || null);
        sync();
    }

    function isSafeLinkHref(href) {
        if (!href) return false;
        if (href.startsWith('//')) return false;
        if (href.startsWith('/') || href.startsWith('#')) return true;
        try {
            const url = new URL(href);
            return ['http:', 'https:', 'mailto:'].includes(url.protocol.toLowerCase());
        } catch (error) {
            return false;
        }
    }

    function textAlignmentFromElement(element) {
        const align = (element.getAttribute('align') || '').trim().toLowerCase();
        if (['left', 'center', 'right'].includes(align)) return align;
        const styleAlign = (element.style && element.style.textAlign ? element.style.textAlign : '').trim().toLowerCase();
        if (['left', 'center', 'right'].includes(styleAlign)) return styleAlign;
        const style = (element.getAttribute('style') || '').toLowerCase();
        const styleMatch = style.match(/(?:^|;)\s*text-align\s*:\s*(left|center|right)\s*(?:;|$)/);
        if (styleMatch) return styleMatch[1];
        const classMatch = (` ${element.className || ''} `).match(/\scms-text-(left|center|right)\s/);
        return classMatch ? classMatch[1] : '';
    }

    function applyPortableTextAlignment(element, alignment) {
        element.classList.remove('cms-text-left', 'cms-text-center', 'cms-text-right');
        element.removeAttribute('align');
        if (element.style) element.style.textAlign = '';
        if (alignment) element.classList.add(`cms-text-${alignment}`);
    }

    function isCmsWidgetElement(element) {
        return element.classList.contains('cms-widget-server-status')
            || element.classList.contains('cms-widget-image-gallery')
            || element.classList.contains('cms-widget-dashboard');
    }

    function hasPortableBlockChild(element) {
        return Boolean(element.querySelector('p,h2,h3,h4,ul,ol,blockquote,figure,div,.cms-widget-server-status,.cms-widget-image-gallery,.cms-widget-dashboard'));
    }

    function normalizePortableContent(root) {
        root.querySelectorAll('p,h2,h3,h4,blockquote,li').forEach(block => {
            applyPortableTextAlignment(block, textAlignmentFromElement(block));
        });
        Array.from(root.querySelectorAll('div')).reverse().forEach(div => {
            if (isCmsWidgetElement(div)) return;
            if (hasPortableBlockChild(div)) return;
            const paragraph = document.createElement('p');
            applyPortableTextAlignment(paragraph, textAlignmentFromElement(div));
            while (div.firstChild) paragraph.appendChild(div.firstChild);
            div.replaceWith(paragraph);
        });
    }

    function sync() {
        const portableContent = editor.cloneNode(true);
        normalizePortableContent(portableContent);
        portableContent.querySelectorAll('img').forEach(image => {
            const source = image.getAttribute('src') || '';
            const match = source.match(/(?:^|\/)uploads\/pages\/([a-f0-9]{32}\.(?:jpg|png|webp))$/i);
            if (match) image.setAttribute('src', `uploads/pages/${match[1]}`);
        });
        textarea.value = portableContent.innerHTML.trim();
    }

    function rememberSelection() {
        const selection = window.getSelection();
        if (selection && selection.rangeCount && editor.contains(selection.anchorNode)) {
            savedRange = selection.getRangeAt(0).cloneRange();
        }
    }

    function restoreSelection() {
        const selection = window.getSelection();
        if (!selection || !savedRange) return;
        selection.removeAllRanges();
        selection.addRange(savedRange);
    }

    wrapper.querySelectorAll('[data-editor-command]').forEach(button => {
        button.addEventListener('mousedown', event => event.preventDefault());
        button.addEventListener('click', () => run(button.dataset.editorCommand));
    });

    format.addEventListener('change', () => {
        run('formatBlock', format.value);
        format.value = 'p';
    });

    const linkButton = wrapper.querySelector('[data-editor-link]');
    linkButton.addEventListener('mousedown', event => event.preventDefault());
    linkButton.addEventListener('click', () => {
        const selection = window.getSelection();
        if (!selection || selection.isCollapsed) {
            focusEditor();
            return;
        }
        const href = window.prompt(text.linkPrompt || 'Enter a link URL (https://...)');
        if (!href) return;
        const cleanHref = href.trim();
        if (!isSafeLinkHref(cleanHref)) {
            window.alert(text.invalidLink || 'Enter a link URL beginning with https://, http://, mailto:, / or #');
            return;
        }
        run('createLink', cleanHref);
    });

    const mediaPanel = document.querySelector('[data-media-panel]');
    const mediaButton = wrapper.querySelector('[data-editor-media]');
    const mediaGrid = document.querySelector('[data-media-grid]');
    const mediaStatus = document.querySelector('[data-media-status]');
    const altInput = document.querySelector('[data-media-alt]');
    const captionInput = document.querySelector('[data-media-caption]');
    const alignmentInput = document.querySelector('[data-media-alignment]');

    function placeCursorInNode(node) {
        const selection = window.getSelection();
        if (!selection) return;
        const range = document.createRange();
        range.selectNodeContents(node);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
        rememberSelection();
    }

    function insertNodeAtSelection(node, addEditableParagraphAfter) {
        focusEditor();
        restoreSelection();
        const selection = window.getSelection();
        if (selection && selection.rangeCount && editor.contains(selection.getRangeAt(0).commonAncestorContainer)) {
            const range = selection.getRangeAt(0);
            let insertionPoint = range.commonAncestorContainer.nodeType === Node.ELEMENT_NODE
                ? range.commonAncestorContainer
                : range.commonAncestorContainer.parentElement;
            while (insertionPoint && insertionPoint.parentElement && insertionPoint.parentElement !== editor) {
                insertionPoint = insertionPoint.parentElement;
            }
            range.deleteContents();
            if (insertionPoint && insertionPoint.parentElement === editor) {
                insertionPoint.after(node);
            } else {
                range.insertNode(node);
            }
            range.setStartAfter(node);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
        } else {
            editor.appendChild(node);
        }
        if (addEditableParagraphAfter) {
            const paragraph = document.createElement('p');
            paragraph.appendChild(document.createElement('br'));
            node.after(paragraph);
            placeCursorInNode(paragraph);
        }
        sync();
    }

    const widgetSelect = wrapper.querySelector('[data-editor-widget]');
    const galleries = Array.isArray(widgetConfig.galleries) ? widgetConfig.galleries : [];
    const dashboardWidgets = widgetConfig.dashboardWidgets || {};
    let serverNamesPromise = null;

    function widgetLabel(server) {
        return `${text.serverStatus || 'Server Status'} — ${server || text.allServers || 'All Servers'}`;
    }

    function decorateWidget(widget) {
        widget.replaceChildren();
        widget.contentEditable = 'false';
        const server = widget.dataset.server || '';
        widget.setAttribute('aria-label', widgetLabel(server));
        const label = document.createElement('strong');
        label.textContent = widgetLabel(server);
        const controls = document.createElement('span');
        controls.className = 'cms-widget-controls';
        const serverSelect = document.createElement('select');
        serverSelect.className = 'cms-editor-format';
        serverSelect.setAttribute('aria-label', text.selectServer || 'Server');
        const allServers = document.createElement('option');
        allServers.value = '';
        allServers.textContent = text.allServers || 'All Servers';
        serverSelect.appendChild(allServers);
        const save = document.createElement('button');
        save.type = 'button';
        save.className = 'cms-widget-save';
        save.textContent = text.saveWidget || 'Save Widget';
        const saved = document.createElement('small');
        saved.className = 'cms-widget-saved';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'cms-widget-remove';
        remove.textContent = text.removeWidget || 'Remove widget';
        save.addEventListener('mousedown', event => event.preventDefault());
        save.addEventListener('click', () => {
            if (serverSelect.value) {
                widget.dataset.server = serverSelect.value;
            } else {
                delete widget.dataset.server;
            }
            label.textContent = widgetLabel(serverSelect.value);
            widget.setAttribute('aria-label', widgetLabel(serverSelect.value));
            saved.textContent = text.widgetSaving || 'Saving…';
            sync();
            if (!form.checkValidity()) {
                saved.textContent = '';
                form.reportValidity();
                return;
            }
            form.requestSubmit();
        });
        remove.addEventListener('mousedown', event => event.preventDefault());
        remove.addEventListener('click', () => {
            widget.remove();
            sync();
            focusEditor();
        });
        serverSelect.addEventListener('change', () => { saved.textContent = ''; });
        controls.append(serverSelect, save, remove, saved);
        widget.append(label, controls);
        loadWidgetServers().then(names => {
            if (names === null) {
                const unavailable = document.createElement('option');
                unavailable.disabled = true;
                unavailable.textContent = text.serversUnavailable || 'Servers unavailable';
                serverSelect.appendChild(unavailable);
                return;
            }
            if (server && !names.includes(server)) names.unshift(server);
            names.forEach(name => {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                serverSelect.appendChild(option);
            });
            serverSelect.value = server;
        });
    }

    function decorateGalleryWidget(widget) {
        widget.replaceChildren();
        widget.contentEditable = 'false';
        const controls = document.createElement('span');
        controls.className = 'cms-widget-controls';
        const label = document.createElement('strong');
        const gallerySelect = document.createElement('select');
        gallerySelect.className = 'cms-editor-format';
        gallerySelect.setAttribute('aria-label', text.selectGallery || 'Gallery');
        galleries.forEach(gallery => {
            const option = document.createElement('option');
            option.value = gallery.id;
            option.textContent = gallery.title;
            gallerySelect.appendChild(option);
        });
        if (widget.dataset.gallery) gallerySelect.value = widget.dataset.gallery;
        const updateLabel = () => {
            const selected = galleries.find(gallery => gallery.id === gallerySelect.value);
            label.textContent = `${text.imageGallery || 'Image Gallery'} — ${selected ? selected.title : ''}`;
            widget.setAttribute('aria-label', label.textContent);
        };
        updateLabel();
        const save = document.createElement('button');
        save.type = 'button';
        save.className = 'cms-widget-save';
        save.textContent = text.saveWidget || 'Save Widget';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'cms-widget-remove';
        remove.textContent = text.removeWidget || 'Remove Widget';
        const saving = document.createElement('small');
        saving.className = 'cms-widget-saved';
        save.addEventListener('mousedown', event => event.preventDefault());
        save.addEventListener('click', () => {
            widget.dataset.gallery = gallerySelect.value;
            updateLabel();
            saving.textContent = text.widgetSaving || 'Saving page…';
            sync();
            if (!form.checkValidity()) {
                saving.textContent = '';
                form.reportValidity();
                return;
            }
            form.requestSubmit();
        });
        remove.addEventListener('mousedown', event => event.preventDefault());
        remove.addEventListener('click', () => {
            widget.remove();
            sync();
            focusEditor();
        });
        gallerySelect.addEventListener('change', () => { saving.textContent = ''; updateLabel(); });
        controls.append(gallerySelect, save, remove, saving);
        widget.append(label, controls);
    }

    function decorateDashboardWidget(widget) {
        widget.replaceChildren();
        widget.contentEditable = 'false';
        const type = widget.dataset.widget || 'summary';
        const title = dashboardWidgets[type] || type;
        const controls = document.createElement('span');
        controls.className = 'cms-widget-controls';
        const label = document.createElement('strong');
        const serverSelect = document.createElement('select');
        serverSelect.className = 'cms-editor-format';
        serverSelect.setAttribute('aria-label', text.selectServer || 'Server');
        const all = document.createElement('option');
        all.value = '';
        all.textContent = text.allServers || 'All Servers';
        serverSelect.appendChild(all);
        const savedServer = widget.dataset.server || '';
        const updateLabel = () => {
            label.textContent = `${title} — ${serverSelect.value || text.allServers || 'All Servers'}`;
            widget.setAttribute('aria-label', label.textContent);
        };
        loadWidgetServers().then(names => {
            if (names === null) {
                const unavailable = document.createElement('option');
                unavailable.disabled = true;
                unavailable.textContent = text.serversUnavailable || 'Servers unavailable';
                serverSelect.appendChild(unavailable);
                return;
            }
            const available = [...names];
            if (savedServer && !available.includes(savedServer)) available.unshift(savedServer);
            available.forEach(name => {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                serverSelect.appendChild(option);
            });
            serverSelect.value = savedServer;
            updateLabel();
        });
        serverSelect.value = savedServer;
        updateLabel();
        controls.appendChild(serverSelect);

        let metricSelect = null;
        if (type === 'top-pilots') {
            metricSelect = document.createElement('select');
            metricSelect.className = 'cms-editor-format';
            metricSelect.setAttribute('aria-label', text.metric || 'Metric');
            Object.entries(widgetConfig.metrics || {}).forEach(([value, labelText]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = labelText;
                metricSelect.appendChild(option);
            });
            metricSelect.value = widget.dataset.metric || 'kills';
            controls.appendChild(metricSelect);
        }

        let limitSelect = null;
        if (['top-pilots', 'top-squadrons', 'player-activity', 'top-theatres', 'top-missions', 'top-modules'].includes(type)) {
            limitSelect = document.createElement('select');
            limitSelect.className = 'cms-editor-format cms-widget-limit';
            limitSelect.setAttribute('aria-label', text.limit || 'Items');
            [3, 5, 10].forEach(value => {
                const option = document.createElement('option');
                option.value = String(value);
                option.textContent = String(value);
                limitSelect.appendChild(option);
            });
            limitSelect.value = widget.dataset.limit || '5';
            controls.appendChild(limitSelect);
        }

        const save = document.createElement('button');
        save.type = 'button';
        save.className = 'cms-widget-save';
        save.textContent = text.saveWidget || 'Save Widget';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'cms-widget-remove';
        remove.textContent = text.removeWidget || 'Remove Widget';
        const saving = document.createElement('small');
        saving.className = 'cms-widget-saved';
        save.addEventListener('mousedown', event => event.preventDefault());
        save.addEventListener('click', () => {
            if (serverSelect.value) widget.dataset.server = serverSelect.value;
            else delete widget.dataset.server;
            if (metricSelect) widget.dataset.metric = metricSelect.value;
            if (limitSelect) widget.dataset.limit = limitSelect.value;
            updateLabel();
            saving.textContent = text.widgetSaving || 'Saving page…';
            sync();
            if (!form.checkValidity()) {
                saving.textContent = '';
                form.reportValidity();
                return;
            }
            form.requestSubmit();
        });
        remove.addEventListener('mousedown', event => event.preventDefault());
        remove.addEventListener('click', () => {
            widget.remove();
            sync();
            focusEditor();
        });
        [serverSelect, metricSelect, limitSelect].filter(Boolean).forEach(select => select.addEventListener('change', () => { saving.textContent = ''; }));
        controls.append(save, remove, saving);
        widget.append(label, controls);
    }

    editor.querySelectorAll('.cms-widget-server-status').forEach(decorateWidget);
    editor.querySelectorAll('.cms-widget-image-gallery').forEach(decorateGalleryWidget);
    editor.querySelectorAll('.cms-widget-dashboard').forEach(decorateDashboardWidget);

    function loadWidgetServers() {
        if (serverNamesPromise) return serverNamesPromise;
        serverNamesPromise = (async () => {
            try {
                const response = await fetch(widgetConfig.serversEndpoint || '../get_servers.php', {credentials: 'same-origin'});
                if (!response.ok) throw new Error('Request failed');
                const result = await response.json();
                const data = result && Object.prototype.hasOwnProperty.call(result, 'data') ? result.data : result;
                const servers = Array.isArray(data) ? data : (data && Array.isArray(data.servers) ? data.servers : []);
                return [...new Set(servers.map(item => String(item.name || item.server_name || '').trim()).filter(Boolean))];
            } catch (error) {
                return null;
            }
        })();
        return serverNamesPromise;
    }

    widgetSelect.addEventListener('mousedown', rememberSelection);
    widgetSelect.addEventListener('change', () => {
        const type = widgetSelect.value;
        widgetSelect.value = '';
        const marker = document.createElement('div');
        if (type === 'server-status') {
            marker.className = 'cms-widget-server-status';
            decorateWidget(marker);
        } else if (type === 'image-gallery' && galleries.length) {
            marker.className = 'cms-widget-image-gallery';
            marker.dataset.gallery = galleries[0].id;
            decorateGalleryWidget(marker);
        } else if (type.startsWith('dashboard:')) {
            marker.className = 'cms-widget-dashboard';
            marker.dataset.widget = type.slice('dashboard:'.length);
            if (marker.dataset.widget === 'top-pilots') marker.dataset.metric = 'kills';
            if (['top-pilots', 'top-squadrons', 'player-activity', 'top-theatres', 'top-missions', 'top-modules'].includes(marker.dataset.widget)) marker.dataset.limit = '5';
            decorateDashboardWidget(marker);
        } else {
            return;
        }
        insertNodeAtSelection(marker, true);
    });

    function setMediaStatus(message, isError) {
        mediaStatus.textContent = message || '';
        mediaStatus.classList.toggle('text-danger', Boolean(isError));
        mediaStatus.classList.toggle('text-success', Boolean(message) && !isError);
    }

    function mediaRequest(formData) {
        return fetch(mediaConfig.endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'X-CSRF-Token': mediaConfig.csrfToken},
            body: formData
        }).then(async response => {
            const responseText = await response.text();
            let payload = {};
            try {
                payload = responseText ? JSON.parse(responseText) : {};
            } catch (error) {
                const detail = response.status === 413
                    ? (text.tooLarge || 'The web server rejected the image because it is too large.')
                    : `${text.requestFailed || 'The media request failed.'} (HTTP ${response.status || 0})`;
                throw new Error(detail);
            }
            if (!response.ok || !payload.success) throw new Error(payload.message || text.requestFailed || 'Request failed.');
            return payload;
        }).catch(error => {
            if (error instanceof TypeError) {
                throw new Error(text.networkFailed || 'The browser could not reach the media endpoint.');
            }
            throw error;
        });
    }

    function insertImage(item) {
        const alt = altInput.value.trim();
        if (!alt) {
            window.alert(text.altRequired || 'Alternative text is required.');
            altInput.focus();
            return;
        }
        const figure = document.createElement('figure');
        figure.className = alignmentInput.value;
        const image = document.createElement('img');
        image.src = '../' + item.path;
        image.alt = alt;
        image.width = Number(item.width) || 0;
        image.height = Number(item.height) || 0;
        image.loading = 'lazy';
        figure.appendChild(image);
        const caption = captionInput.value.trim();
        if (caption) {
            const figcaption = document.createElement('figcaption');
            figcaption.textContent = caption;
            figure.appendChild(figcaption);
        }
        insertNodeAtSelection(figure);
        altInput.value = '';
        captionInput.value = '';
        mediaPanel.hidden = true;
    }

    function renderMedia() {
        mediaGrid.replaceChildren();
        if (!mediaItems.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted';
            empty.textContent = text.emptyLibrary || 'No images uploaded yet.';
            mediaGrid.appendChild(empty);
            return;
        }
        mediaItems.forEach(item => {
            const card = document.createElement('div');
            card.className = 'cms-media-card';
            const image = document.createElement('img');
            image.src = '../' + item.path;
            image.alt = '';
            image.loading = 'lazy';
            const details = document.createElement('small');
            details.textContent = `${item.original_name} · ${item.width}×${item.height}`;
            const actions = document.createElement('div');
            actions.className = 'cms-media-card-actions';
            const insert = document.createElement('button');
            insert.type = 'button';
            insert.className = 'btn btn-primary btn-small';
            insert.textContent = text.insertImage || 'Insert';
            insert.addEventListener('click', () => insertImage(item));
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-danger btn-small';
            remove.textContent = text.deleteImage || 'Delete';
            remove.addEventListener('click', () => {
                if (editor.innerHTML.includes(item.path)) {
                    window.alert(text.imageInEditor || 'Remove this image from the current page before deleting it.');
                    return;
                }
                if (!window.confirm(text.deleteConfirm || 'Delete this image?')) return;
                const data = new FormData();
                data.append('action', 'delete');
                data.append('media_id', item.id);
                mediaRequest(data).then(payload => {
                    mediaItems = mediaItems.filter(existing => existing.id !== item.id);
                    setMediaStatus(payload.message, false);
                    renderMedia();
                }).catch(error => setMediaStatus(error.message, true));
            });
            actions.append(insert, remove);
            card.append(image, details, actions);
            mediaGrid.appendChild(card);
        });
    }

    mediaButton.addEventListener('mousedown', event => event.preventDefault());
    mediaButton.addEventListener('click', () => {
        rememberSelection();
        mediaPanel.hidden = !mediaPanel.hidden;
        if (!mediaPanel.hidden) renderMedia();
    });
    document.querySelector('[data-media-close]').addEventListener('click', () => { mediaPanel.hidden = true; });
    document.querySelector('[data-media-upload]').addEventListener('click', () => {
        const fileInput = document.querySelector('[data-media-file]');
        if (!fileInput.files.length) return;
        const data = new FormData();
        data.append('action', 'upload');
        data.append('image', fileInput.files[0]);
        setMediaStatus(text.uploading || 'Uploading…', false);
        mediaRequest(data).then(payload => {
            mediaItems.unshift(payload.item);
            fileInput.value = '';
            setMediaStatus('', false);
            renderMedia();
        }).catch(error => setMediaStatus(error.message, true));
    });

    editor.addEventListener('input', sync);
    editor.addEventListener('keyup', rememberSelection);
    editor.addEventListener('mouseup', rememberSelection);
    editor.addEventListener('paste', event => {
        event.preventDefault();
        const plainText = (event.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, plainText);
    });
    form.addEventListener('submit', event => {
        sync();
        if (!editor.textContent.trim() && !editor.querySelector('.cms-widget-server-status')) {
            event.preventDefault();
            window.alert(text.contentRequired || 'Page content is required.');
            focusEditor();
        }
    });
    sync();
}());
