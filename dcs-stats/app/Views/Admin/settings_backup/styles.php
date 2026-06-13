<style>
        .backup-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .settings-note {
            background-color: rgba(76, 175, 80, 0.08);
            border: 1px solid rgba(76, 175, 80, 0.28);
            border-radius: 6px;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 20px;
            padding: 15px;
        }

        .included-list,
        .excluded-list {
            display: grid;
            gap: 8px;
            margin: 12px 0 0;
            padding-left: 18px;
        }

        .file-input-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 14px 0;
        }
    </style>