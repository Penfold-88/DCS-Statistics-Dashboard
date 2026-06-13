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