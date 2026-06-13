<style>
.api-section {
    margin-top: 0;
}

.section-heading {
    margin-bottom: 22px;
    text-align: center;
}

.section-heading h2 {
    color: var(--heading_color);
    font-size: 2rem;
    margin: 0 0 8px;
    text-shadow: 0 0 10px color-mix(in srgb, var(--accent_color) 30%, transparent);
}

.section-heading p {
    color: var(--muted_text_color);
    font-size: 1rem;
    line-height: 1.5;
    margin: 0;
}

.server-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
    gap: 22px;
}

.server-detail-card {
    background: linear-gradient(135deg, var(--card_color) 0%, var(--card_alt_color) 100%);
    border: 1px solid color-mix(in srgb, var(--accent_color) 36%, transparent);
    border-left: 4px solid color-mix(in srgb, var(--accent_color) 75%, transparent);
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    color: var(--card_text_color);
    overflow: hidden;
    padding: 22px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.server-detail-card * {
    min-width: 0;
}

.server-detail-card:hover {
    border-color: var(--accent_color);
    box-shadow: 0 12px 40px color-mix(in srgb, var(--accent_color) 25%, transparent);
    transform: translateY(-2px);
}

.server-detail-header {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    border-bottom: 1px solid color-mix(in srgb, var(--border_color) 55%, transparent);
    padding-bottom: 16px;
    margin-bottom: 16px;
}

.server-detail-header > div {
    flex: 1 1 auto;
    min-width: 0;
}

.server-detail-header h3 {
    color: var(--card_heading_color);
    font-size: 1.15rem;
    line-height: 1.3;
    margin: 0 0 8px;
    text-shadow: 0 0 10px color-mix(in srgb, var(--accent_color) 28%, transparent);
}

.server-detail-header p {
    color: var(--card_muted_text_color);
    font-size: 0.92rem;
    margin: 0;
    line-height: 1.4;
}

.server-description {
    max-height: 8.4em;
    overflow-y: auto;
    overflow-wrap: anywhere;
    padding-right: 6px;
    scrollbar-width: thin;
    word-break: normal;
}

.detail-status {
    background: color-mix(in srgb, var(--warning_color) 12%, transparent);
    border: 1px solid color-mix(in srgb, var(--warning_color) 35%, transparent);
    border-radius: 999px;
    color: var(--warning_color);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    padding: 6px 10px;
    text-transform: uppercase;
    white-space: nowrap;
}

.detail-metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}

.detail-metrics div {
    background: color-mix(in srgb, var(--secondary_color) 84%, transparent);
    border: 1px solid color-mix(in srgb, var(--border_color) 55%, transparent);
    border-radius: 6px;
    padding: 12px;
}

.detail-metrics span {
    display: block;
    color: var(--card_heading_color);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.detail-metrics strong {
    display: block;
    color: var(--card_text_color);
    font-size: 0.98rem;
    line-height: 1.35;
    margin-top: 6px;
    overflow-wrap: anywhere;
    word-break: normal;
}

.detail-metrics .mission-metric {
    grid-column: 1 / -1;
}

.detail-metrics .mission-metric strong {
    font-size: clamp(0.86rem, 1.2vw, 0.98rem);
    line-height: 1.45;
    white-space: normal;
}

.detail-split {
    display: grid;
    gap: 14px;
}

.detail-split section {
    background: color-mix(in srgb, var(--secondary_color) 84%, transparent);
    border: 1px solid color-mix(in srgb, var(--border_color) 55%, transparent);
    border-radius: 6px;
    padding: 14px;
}

.detail-split h4 {
    color: var(--card_heading_color);
    font-size: 0.95rem;
    letter-spacing: 0.03em;
    margin: 0 0 10px;
    text-shadow: 0 0 8px color-mix(in srgb, var(--accent_color) 25%, transparent);
}

.detail-split p {
    margin: 0;
    color: var(--card_text_color);
    overflow-wrap: anywhere;
}

.detail-list-item {
    display: grid;
    gap: 3px;
    padding: 9px 0;
    border-top: 1px solid color-mix(in srgb, var(--border_color) 55%, transparent);
}

.detail-list-item:first-of-type {
    border-top: 0;
}

.detail-list-item strong {
    color: var(--card_heading_color);
    font-size: 0.95rem;
}

.detail-list-item span,
.detail-list-item small,
.muted {
    color: var(--card_muted_text_color);
}

.detail-list-item small {
    overflow-wrap: anywhere;
    white-space: pre-line;
}

.detail-status.status-online,
.detail-status.status-running {
    background: color-mix(in srgb, var(--success_color) 12%, transparent);
    border-color: color-mix(in srgb, var(--success_color) 42%, transparent);
    color: var(--success_color);
}

.detail-status.status-offline,
.detail-status.status-shutdown {
    background: color-mix(in srgb, var(--danger_color) 12%, transparent);
    border-color: color-mix(in srgb, var(--danger_color) 42%, transparent);
    color: var(--danger_color);
}

.detail-status.status-starting,
.detail-status.status-paused {
    background: color-mix(in srgb, var(--warning_color) 12%, transparent);
    border-color: color-mix(in srgb, var(--warning_color) 42%, transparent);
    color: var(--warning_color);
}

.detail-status.status-unknown {
    background: color-mix(in srgb, var(--muted_text_color) 12%, transparent);
    border-color: color-mix(in srgb, var(--muted_text_color) 35%, transparent);
    color: var(--card_muted_text_color);
}

/* Mobile Responsive Styles */
@media screen and (max-width: 768px) {
    /* Dashboard header mobile */
    .dashboard-header h1 {
        font-size: 1.8rem;
    }
    
    .dashboard-subtitle {
        font-size: 0.9rem;
        padding: 0 10px;
    }
    
    /* Loading and no-servers messages */
    #servers-loading,
    #no-servers {
        padding: 30px 15px !important;
        font-size: 1rem;
    }

    .server-details-grid {
        grid-template-columns: 1fr;
    }

    .server-detail-header {
        display: grid;
    }

    .detail-status {
        justify-self: start;
    }

    .detail-metrics {
        grid-template-columns: 1fr;
    }
}

/* Ensure hide-mobile works */
@media screen and (max-width: 768px) {
    .hide-mobile {
        display: none !important;
    }
}
</style>