<style>
        .update-log {
            background: #111;
            color: #0f0;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            font-family: monospace;
        }
        .warning-box {
            background-color: rgba(255, 152, 0, 0.1);
            border: 1px solid var(--accent-warning);
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .col-md-6 {
            flex: 1;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-primary {
            background-color: var(--accent-primary);
            color: white;
        }
        .badge-warning {
            background-color: var(--accent-warning);
            color: #000;
        }
        .btn-block {
            width: 100%;
            display: block;
        }
        .btn-info {
            background-color: var(--accent-info);
            color: white;
        }
        .btn-info:hover {
            background-color: #1976D2;
        }
        .mb-2 {
            margin-bottom: 10px;
        }
        .badge-info {
            background-color: #2196F3;
            color: white;
        }
        .badge-success {
            background-color: #4CAF50;
            color: white;
        }
        .mt-2 {
            margin-top: 10px;
        }
        .version-summary-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            margin-bottom: 16px;
        }
        .version-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px;
        }
        .version-label {
            color: var(--text-muted);
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .version-value {
            color: var(--text-primary);
            display: block;
            font-size: 18px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }
        .version-details {
            display: grid;
            gap: 8px;
            margin: 0 0 14px;
        }
        .version-detail-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: space-between;
        }
        .version-detail-row strong {
            color: var(--text-muted);
        }
        .version-status-panel {
            background: rgba(33, 150, 243, 0.08);
            border: 1px solid rgba(33, 150, 243, 0.22);
            border-radius: 6px;
            margin-top: 14px;
            padding: 12px;
        }
        .version-status-title {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 6px;
        }
        .version-status-meta {
            color: var(--text-muted);
            display: grid;
            gap: 4px;
            font-size: 13px;
        }
    </style>