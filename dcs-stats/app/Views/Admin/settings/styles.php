<style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .settings-group {
            background-color: var(--bg-tertiary);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .settings-group.collapsible-group {
            padding: 0;
        }
        
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            margin-bottom: 0;
            color: var(--accent-primary);
            font-size: 18px;
            cursor: pointer;
            user-select: none;
            background-color: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }
        
        .group-header:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }
        
        .collapse-arrow {
            font-size: 14px;
            transition: transform 0.3s ease;
        }
        
        .group-header.collapsed .collapse-arrow {
            transform: rotate(-90deg);
        }
        
        .group-content {
            padding: 20px;
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
        
        .group-content.collapsed {
            max-height: 0;
            padding: 0 20px;
        }
        
        .setting-item {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        
        .setting-item input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .setting-item label {
            cursor: pointer;
            flex: 1;
            user-select: none;
        }
        
        .setting-item.dependent {
            margin-left: 25px;
            opacity: 0.8;
        }
        
        .setting-item.disabled {
            opacity: 0.5;
        }
        
        .setting-item.disabled label {
            cursor: not-allowed;
        }

        .setting-item.locked-feature input[type="checkbox"] {
            cursor: not-allowed;
        }

        .settings-subheading {
            border-top: 1px solid var(--border-color);
            color: var(--accent-primary);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.06em;
            margin: 18px 0 12px;
            padding-top: 16px;
            text-transform: uppercase;
        }

        .setting-item.dynamic-server {
            align-items: flex-start;
        }

        .setting-item.dynamic-server input[type="checkbox"] {
            flex: 0 0 auto;
            margin-top: 2px;
        }

        .setting-item.dynamic-server label {
            font-size: clamp(12px, 1.2vw, 14px);
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        
        .settings-actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .warning-box {
            background-color: rgba(255, 152, 0, 0.1);
            border: 1px solid var(--accent-warning);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .bulk-actions {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

    </style>