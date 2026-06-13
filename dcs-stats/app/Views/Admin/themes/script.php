<script>
    const themeText = <?= json_encode([
        'noFileSelected' => dcs_t('admin.themes.no_file_selected'),
        'noNewImageSelected' => dcs_t('admin.themes.no_new_image_selected'),
        'defaultImageSelected' => dcs_t('admin.themes.default_image_selected'),
        'noNewBackgroundSelected' => dcs_t('admin.themes.no_new_background_selected'),
        'backgroundWillBeRemoved' => dcs_t('admin.themes.background_will_be_removed'),
        'noNewLogoSelected' => dcs_t('admin.themes.no_new_logo_selected'),
        'logoWillBeRemoved' => dcs_t('admin.themes.logo_will_be_removed'),
        'updatingPreview' => dcs_t('admin.themes.updating_preview'),
        'previewUpdated' => dcs_t('admin.themes.preview_updated'),
        'resetMenuConfirm' => dcs_t('admin.themes.reset_menu_confirm'),
        'colorsRestored' => dcs_t('admin.themes.colors_restored')
    ], JSON_UNESCAPED_UNICODE) ?>;

    // Tab switching
    function switchTab(tabName) {
        // Remove active class from all tabs and contents
        document.querySelectorAll('.theme-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        // Add active class to selected tab and content
        event.target.classList.add('active');
        document.getElementById(tabName + '-tab').classList.add('active');
    }

    // File input handling
    const cssFileInput = document.getElementById('css_file');
    if (cssFileInput) {
        cssFileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || themeText.noFileSelected;
            document.getElementById('file-name').textContent = fileName;
        });
    }

    const headerImageInput = document.getElementById('header_image');
    const headerImagePreview = document.querySelector('.header-image-preview');
    const positionX = document.getElementById('position_x');
    const positionY = document.getElementById('position_y');
    const useDefaultHeaderImage = document.getElementById('use_default_header_image');
    const defaultHeaderImageUrl = '../dcs-header-image.jpg';
    const backgroundImageInput = document.getElementById('background_image');
    const backgroundImagePreview = document.querySelector('.page-background-preview');
    const backgroundPositionX = document.getElementById('background_position_x');
    const backgroundPositionY = document.getElementById('background_position_y');
    const backgroundZoom = document.getElementById('background_zoom');
    const removeBackgroundImage = document.getElementById('remove_background_image');
    const headerLogoInput = document.getElementById('header_logo');
    const headerLogoPreview = document.querySelector('.header-logo-preview');
    const logoHeight = document.getElementById('logo_height');
    const removeHeaderLogo = document.getElementById('remove_header_logo');

    function updateHeaderImagePreview() {
        if (!headerImagePreview || !positionX || !positionY) return;
        headerImagePreview.style.backgroundPosition = `${positionX.value}% ${positionY.value}%`;
    }

    if (headerImageInput) {
        headerImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            document.getElementById('header-image-file-name').textContent = file?.name || themeText.noNewImageSelected;
            if (file && headerImagePreview) {
                if (useDefaultHeaderImage) useDefaultHeaderImage.checked = false;
                headerImagePreview.style.backgroundImage = `url('${URL.createObjectURL(file)}')`;
                updateHeaderImagePreview();
            }
        });
    }

    const themeSettingsInput = document.getElementById('theme_settings_file');
    if (themeSettingsInput) {
        themeSettingsInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || themeText.noFileSelected;
            document.getElementById('theme-settings-file-name').textContent = fileName;
        });
    }

    if (useDefaultHeaderImage) {
        useDefaultHeaderImage.addEventListener('change', function() {
            if (this.checked && headerImagePreview) {
                headerImagePreview.style.backgroundImage = `url('${defaultHeaderImageUrl}')`;
                document.getElementById('header-image-file-name').textContent = themeText.defaultImageSelected;
                if (headerImageInput) headerImageInput.value = '';
                updateHeaderImagePreview();
            }
        });
    }

    [positionX, positionY].forEach(input => {
        if (input) {
            input.addEventListener('input', updateHeaderImagePreview);
        }
    });

    function updateBackgroundImagePreview() {
        if (!backgroundImagePreview || !backgroundPositionX || !backgroundPositionY) return;
        backgroundImagePreview.style.backgroundPosition = `${backgroundPositionX.value}% ${backgroundPositionY.value}%`;
        if (backgroundZoom) {
            backgroundImagePreview.style.backgroundSize = `${backgroundZoom.value}% auto`;
        }
    }

    if (backgroundImageInput) {
        backgroundImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            document.getElementById('background-image-file-name').textContent = file?.name || themeText.noNewBackgroundSelected;
            if (file && backgroundImagePreview) {
                if (removeBackgroundImage) removeBackgroundImage.checked = false;
                backgroundImagePreview.style.backgroundImage = `url('${URL.createObjectURL(file)}')`;
                const label = backgroundImagePreview.querySelector('span');
                if (label) label.textContent = file.name;
                updateBackgroundImagePreview();
            }
        });
    }

    [backgroundPositionX, backgroundPositionY, backgroundZoom].forEach(input => {
        if (input) {
            input.addEventListener('input', updateBackgroundImagePreview);
        }
    });

    if (removeBackgroundImage && backgroundImagePreview) {
        removeBackgroundImage.addEventListener('change', function() {
            if (this.checked) {
                backgroundImagePreview.style.backgroundImage = '';
                const label = backgroundImagePreview.querySelector('span');
                if (label) label.textContent = themeText.backgroundWillBeRemoved;
                document.getElementById('background-image-file-name').textContent = themeText.noNewBackgroundSelected;
                if (backgroundImageInput) backgroundImageInput.value = '';
            }
        });
    }

    if (headerLogoInput) {
        headerLogoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            document.getElementById('header-logo-file-name').textContent = file?.name || themeText.noNewLogoSelected;
            if (file && headerLogoPreview) {
                if (removeHeaderLogo) removeHeaderLogo.checked = false;
                headerLogoPreview.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="Header logo preview">`;
            }
        });
    }

    if (logoHeight && headerLogoPreview) {
        logoHeight.addEventListener('input', function() {
            headerLogoPreview.style.setProperty('--preview_logo_height', `${this.value}px`);
        });
    }

    if (removeHeaderLogo && headerLogoPreview) {
        removeHeaderLogo.addEventListener('change', function() {
            if (this.checked) {
                headerLogoPreview.innerHTML = `<span class="header-logo-placeholder">${themeText.logoWillBeRemoved}</span>`;
                document.getElementById('header-logo-file-name').textContent = themeText.noNewLogoSelected;
                if (headerLogoInput) headerLogoInput.value = '';
            }
        });
    }


    // Debounce function to prevent too many updates
    let updateTimeout;
    function debounce(func, wait) {
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(updateTimeout);
                func(...args);
            };
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(later, wait);
        };
    }

    // Live color preview
    function updatePreviewColors() {
        const iframe = document.getElementById('preview-frame');

        // Build URL with preview parameters
        const params = new URLSearchParams();
        params.set('preview', '1');
        document.querySelectorAll('#simple-tab input[type="color"]').forEach(input => {
            params.set(input.id, input.value.replace('#', ''));
        });
        const titleGradient = document.getElementById('header_title_gradient_enabled');
        if (titleGradient) {
            params.set('header_title_gradient_enabled', titleGradient.checked ? '1' : '0');
        }
        const pageGradient = document.getElementById('page_background_gradient_enabled');
        if (pageGradient) {
            params.set('page_background_gradient_enabled', pageGradient.checked ? '1' : '0');
        }

        // Update iframe source with preview parameters
        // Build URL to parent directory
        const protocol = window.location.protocol;
        const host = window.location.host;
        const currentPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
        const parentPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
        const baseUrl = protocol + '//' + host + parentPath + '/index.php';
        iframe.src = baseUrl + '?' + params.toString();
    }

    // Debounced version of updatePreviewColors
    const debouncedUpdate = debounce(updatePreviewColors, 500);

    // Add event listeners for real-time updates
    document.querySelectorAll('input[type="color"]').forEach(input => {
        input.addEventListener('input', function() {
            // Show status message immediately
            const status = document.getElementById('preview-status');
            status.textContent = themeText.updatingPreview;
            status.style.color = '#ff9800';

            // Update preview with debounce
            debouncedUpdate();
        });

        input.addEventListener('change', function() {
            // Show completed message
            const status = document.getElementById('preview-status');
            status.textContent = themeText.previewUpdated;
            status.style.color = '#4CAF50';
            setTimeout(() => {
                status.textContent = '';
            }, 2000);
        });
    });

    const titleGradientToggle = document.getElementById('header_title_gradient_enabled');
    if (titleGradientToggle) {
        titleGradientToggle.addEventListener('change', function() {
            updatePreviewColors();
            const status = document.getElementById('preview-status');
            status.textContent = themeText.previewUpdated;
            status.style.color = '#4CAF50';
            setTimeout(() => {
                status.textContent = '';
            }, 2000);
        });
    }
    const pageGradientToggle = document.getElementById('page_background_gradient_enabled');
    if (pageGradientToggle) {
        pageGradientToggle.addEventListener('change', function() {
            updatePreviewColors();
            const status = document.getElementById('preview-status');
            status.textContent = themeText.previewUpdated;
            status.style.color = '#4CAF50';
            setTimeout(() => {
                status.textContent = '';
            }, 2000);
        });
    }

    // Menu drag and drop functionality
    let draggedElement = null;

    function initMenuDragDrop() {
        const menuItems = document.querySelectorAll('.menu-item');

        menuItems.forEach(item => {
            item.draggable = true;

            item.addEventListener('dragstart', function(e) {
                draggedElement = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', this.innerHTML);
            });

            item.addEventListener('dragend', function() {
                this.classList.remove('dragging');
            });

            item.addEventListener('dragover', function(e) {
                if (e.preventDefault) {
                    e.preventDefault();
                }
                e.dataTransfer.dropEffect = 'move';
                this.classList.add('drag-over');
                return false;
            });

            item.addEventListener('dragleave', function() {
                this.classList.remove('drag-over');
            });

            item.addEventListener('drop', function(e) {
                if (e.stopPropagation) {
                    e.stopPropagation();
                }

                this.classList.remove('drag-over');

                if (draggedElement !== this) {
                    const container = document.getElementById('menu-items');
                    const allItems = Array.from(container.querySelectorAll('.menu-item'));
                    const draggedIndex = allItems.indexOf(draggedElement);
                    const targetIndex = allItems.indexOf(this);

                    if (draggedIndex < targetIndex) {
                        this.parentNode.insertBefore(draggedElement, this.nextSibling);
                    } else {
                        this.parentNode.insertBefore(draggedElement, this);
                    }

                    // Update order inputs
                    updateMenuOrder();
                }

                return false;
            });
        });
    }

    function updateMenuOrder() {
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach((item, index) => {
            const orderInput = item.querySelector('input[name="menu_order[]"]');
            orderInput.value = index;

            // Update field names to match new order
            const nameInput = item.querySelector('input[name^="menu_names"]');
            const urlInput = item.querySelector('input[name^="menu_urls"]');
            const enabledInput = item.querySelector('input[name^="menu_enabled"]');
            const typeInput = item.querySelector('input[name^="menu_types"]');

            nameInput.name = `menu_names[${index}]`;
            urlInput.name = `menu_urls[${index}]`;
            enabledInput.name = `menu_enabled[${index}]`;
            typeInput.name = `menu_types[${index}]`;
        });
    }

    function resetMenu() {
        if (confirm(themeText.resetMenuConfirm)) {
            // Create a form to submit reset action
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="update_menu">
                <?php foreach ($defaultMenuItems as $index => $item): ?>
                <input type="hidden" name="menu_order[]" value="<?= $index ?>">
                <input type="hidden" name="menu_names[<?= $index ?>]" value="<?= htmlspecialchars($item['name']) ?>">
                <input type="hidden" name="menu_urls[<?= $index ?>]" value="<?= htmlspecialchars($item['url']) ?>">
                <input type="hidden" name="menu_types[<?= $index ?>]" value="<?= htmlspecialchars($item['type']) ?>">
                <?php if ($item['enabled']): ?>
                <input type="hidden" name="menu_enabled[<?= $index ?>]" value="1">
                <?php endif; ?>
                <?php endforeach; ?>
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Restore default colors
    function restoreDefaultColors() {
        const defaults = <?= json_encode($defaultThemeColors) ?>;

        // Set the color inputs to default values
        for (const [id, value] of Object.entries(defaults)) {
            const input = document.getElementById(id);
            if (input) input.value = value;
        }
        const titleGradient = document.getElementById('header_title_gradient_enabled');
        if (titleGradient) titleGradient.checked = false;
        const pageGradient = document.getElementById('page_background_gradient_enabled');
        if (pageGradient) pageGradient.checked = false;

        // Update preview immediately
        updatePreviewColors();

        // Show status message
        const status = document.getElementById('preview-status');
        status.textContent = themeText.colorsRestored;
        status.style.color = '#2196F3';
        setTimeout(() => {
            status.textContent = '';
        }, 3000);
    }

    function restoreDefaultChartColors() {
        const defaults = <?= json_encode($defaultChartTheme) ?>;

        for (const [id, value] of Object.entries(defaults)) {
            const input = document.getElementById(id);
            if (input) input.value = value;
        }
    }

    // Initialize drag and drop when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initMenuDragDrop();

        // Initialize preview with current colors
        updatePreviewColors();
    });
</script>
