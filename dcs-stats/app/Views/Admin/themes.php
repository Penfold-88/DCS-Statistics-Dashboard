<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Critical inline CSS for layout */
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .card { max-width: 100%; overflow-x: auto; }
        
        .theme-section {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .preset-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin-top: 18px;
        }

        .preset-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            justify-content: space-between;
            padding: 16px;
        }

        .preset-card h3 {
            color: var(--text-primary);
            font-size: 18px;
            margin: 0 0 6px;
        }

        .preset-card p {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0;
        }

        .preset-swatches {
            display: flex;
            gap: 6px;
            margin-top: 12px;
        }

        .preset-swatch {
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            height: 24px;
            width: 24px;
        }

        .preset-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .save-preset-row {
            align-items: flex-end;
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(220px, 420px) auto;
            margin-top: 16px;
        }

        .save-preset-row input[type="text"] {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-primary);
            padding: 10px 12px;
            width: 100%;
        }
        
        .color-inputs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
            max-width: 900px;
        }

        .color-fieldset {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin: 22px 0;
            padding: 16px;
        }

        .color-fieldset legend {
            color: var(--accent-primary);
            font-weight: 700;
            padding: 0 8px;
        }
        
        .color-input-group {
            display: flex;
            align-items: center;
            background: var(--bg-tertiary);
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .color-input-group:hover {
            border-color: var(--accent-primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .color-input-group label {
            flex: 1;
            font-size: 0.95em;
            font-weight: 500;
            cursor: pointer;
        }
        
        .color-input-group input[type="color"] {
            width: 60px;
            height: 40px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
            padding: 2px;
            background: var(--bg-secondary);
            transition: all 0.2s ease;
        }
        
        .color-input-group input[type="color"]:hover {
            border-color: var(--accent-primary);
            transform: scale(1.05);
        }
        
        .color-input-group input[type="color"]::-webkit-color-swatch {
            border-radius: 4px;
            border: none;
        }
        
        .color-input-group input[type="color"]::-moz-color-swatch {
            border-radius: 4px;
            border: none;
        }
        
        @media (max-width: 600px) {
            .color-inputs {
                grid-template-columns: 1fr;
            }
            .save-preset-row {
                grid-template-columns: 1fr;
            }
        }
        
        .upload-section {
            margin-top: 20px;
        }

        .header-image-preview {
            align-items: flex-end;
            aspect-ratio: 24 / 5;
            background-color: #111;
            background-repeat: no-repeat;
            background-size: cover;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            margin: 20px 0;
            max-width: 960px;
            min-height: 150px;
            overflow: hidden;
            padding: 16px;
        }

        .header-image-preview span {
            background: rgba(0, 0, 0, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 6px;
            color: #fff;
            font-weight: 700;
            padding: 8px 12px;
        }

        .page-background-preview {
            align-items: flex-end;
            aspect-ratio: 16 / 6;
            background-color: var(--bg-tertiary);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            margin: 20px 0;
            max-width: 960px;
            min-height: 180px;
            overflow: hidden;
            padding: 16px;
        }

        .page-background-preview span {
            background: rgba(0, 0, 0, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 6px;
            color: #fff;
            font-weight: 700;
            padding: 8px 12px;
        }

        .header-logo-preview {
            align-items: center;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            gap: 16px;
            margin: 20px 0;
            max-width: 720px;
            min-height: 108px;
            padding: 16px;
        }

        .header-logo-preview img {
            height: var(--preview_logo_height, 72px);
            max-height: 96px;
            max-width: 320px;
            object-fit: contain;
            width: auto;
        }

        .header-logo-placeholder {
            color: var(--text-muted);
            font-weight: 600;
        }

        .header-position-controls {
            display: grid;
            gap: 16px;
            margin-top: 20px;
            max-width: 720px;
        }

        .header-position-controls label {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: grid;
            font-weight: 600;
            gap: 8px;
            padding: 14px 16px;
        }

        .header-position-controls input[type="range"] {
            width: 100%;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-button {
            display: inline-block;
            padding: 10px 20px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-input-button:hover {
            background: var(--bg-primary);
        }
        
        .backup-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .backup-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 5px;
            background: var(--bg-tertiary);
            border-radius: 4px;
        }
        
        .backup-info {
            flex: 1;
        }
        
        .preview-frame {
            width: 100%;
            height: 500px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: #fff;
        }
        
        .theme-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .theme-tab {
            padding: 10px 20px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .theme-tab.active {
            color: var(--text-primary);
            border-bottom-color: var(--accent-primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Menu Configuration Styles */
        .menu-items {
            margin-top: 20px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            margin-bottom: 10px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .menu-item.dragging {
            opacity: 0.5;
        }
        
        .menu-item.drag-over {
            border-color: var(--accent-primary);
            border-style: dashed;
        }
        
        .menu-item-handle {
            cursor: grab;
            font-size: 20px;
            color: var(--text-muted);
            user-select: none;
        }
        
        .menu-item-handle:active {
            cursor: grabbing;
        }
        
        .menu-item-fields {
            display: flex;
            gap: 15px;
            flex: 1;
            align-items: center;
        }
        
        .menu-item-fields input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-primary);
        }
        
        .menu-item-fields input[type="text"]:focus {
            border-color: var(--accent-primary);
            outline: none;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php require DCS_ROOT_PATH . '/app/Views/Admin/partials/nav.php'; ?>
        
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <h1><?= $pageTitle ?></h1>
                <div class="admin-user-menu">
                    <div class="admin-user-info">
                        <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                        <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
                    </div>
                    <a href="logout.php" class="btn btn-secondary btn-small"><?= e(dcs_t('admin.common.logout')) ?></a>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <div class="card">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <!-- Theme Preview Section -->
                <div class="theme-section">
                    <h2><?= e(dcs_t('admin.themes.preview')) ?></h2>
                    <p><?= e(dcs_t('admin.themes.preview_help')) ?></p>
                    
                    <iframe src="<?= $previewUrl ?>" class="preview-frame" id="preview-frame"></iframe>
                    
                    <div style="margin-top: 10px;">
                        <span id="preview-status" style="color: var(--text-muted); font-size: 0.9em;"></span>
                    </div>
                </div>
                
                <!-- Theme Tabs -->
                <div class="theme-tabs">
                    <button class="theme-tab active" onclick="switchTab('simple')"><?= e(dcs_t('admin.themes.simple_customization')) ?></button>
                    <button class="theme-tab" onclick="switchTab('presets')"><?= e(dcs_t('admin.themes.theme_presets')) ?></button>
                    <button class="theme-tab" onclick="switchTab('header-image')"><?= e(dcs_t('admin.themes.header_image')) ?></button>
                    <button class="theme-tab" onclick="switchTab('menu')"><?= e(dcs_t('admin.themes.menu_configuration')) ?></button>
                    <button class="theme-tab" onclick="switchTab('charts')"><?= e(dcs_t('admin.themes.chart_colours')) ?></button>
                    <?php if ($isAirBoss): ?>
                    <button class="theme-tab" onclick="switchTab('advanced')"><?= e(dcs_t('admin.themes.advanced_css_upload')) ?></button>
                    <button class="theme-tab" onclick="switchTab('backups')"><?= e(dcs_t('admin.themes.backup_restore')) ?></button>
                    <?php endif; ?>
                </div>
                
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/simple_tab.php'; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/presets_tab.php'; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/header_image_tab.php'; ?>
                
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/charts_tab.php'; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/menu_tab.php'; ?>
                
                <?php if ($isAirBoss): ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/advanced_tab.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/backups_tab.php'; ?>
                <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
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
</body>
</html>
