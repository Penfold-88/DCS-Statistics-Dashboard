<style>
main {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeInDown 0.8s ease-out;
}

.dashboard-header h1 {
    font-size: 2.5rem;
    color: #4CAF50;
    margin-bottom: 10px;
    text-shadow: 0 0 20px rgba(76, 175, 80, 0.5);
}

.dashboard-subtitle {
    color: #ccc;
    font-size: 1.1rem;
    opacity: 0.8;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 50px;
}

.stat-card {
    background: linear-gradient(135deg, #2c2c2c 0%, #1e1e1e 100%);
    border-radius: 16px;
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    opacity: 0;
    transform: translateY(20px);
}

.stat-card.pop-in {
    animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(76, 175, 80, 0.3);
    border-color: rgba(76, 175, 80, 0.5);
}

.stat-icon {
    font-size: 3rem;
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
    flex: 0 0 auto;
}

.stat-content {
    min-width: 0;
    flex: 1 1 auto;
}

.stat-content h3 {
    color: #ccc;
    font-size: 1rem;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    line-height: 1.15;
    max-width: 100%;
    overflow-wrap: anywhere;
    word-break: normal;
    hyphens: auto;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #4CAF50;
    margin: 0;
    text-shadow: 0 0 15px rgba(76, 175, 80, 0.5);
}

.charts-dashboard {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin: 40px auto;
    width: 100%;
    max-width: 100%;
    padding: 0 20px;
}

.api-attendance,
.api-insights {
    margin: 0 20px 40px;
}

.api-attendance .insight-cards {
    margin-bottom: 0;
}

.section-heading {
    margin-bottom: 18px;
}

.section-heading h2 {
    color: #4CAF50;
    margin: 0 0 6px;
}

.section-heading p {
    color: #ccc;
    margin: 0;
}

.insight-cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.insight-card,
.insight-panel {
    background: linear-gradient(135deg, #2c2c2c 0%, #1e1e1e 100%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.insight-card:hover,
.insight-panel:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(76, 175, 80, 0.3);
    border-color: rgba(76, 175, 80, 0.5);
}

.insight-card {
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 18px;
}

.insight-icon {
    font-size: 2.4rem;
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
    flex: 0 0 auto;
}

.insight-content {
    min-width: 0;
}

.insight-card span {
    color: #ccc;
    display: block;
    margin-bottom: 10px;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
}

.insight-card strong {
    color: #4CAF50;
    font-size: 2rem;
    text-shadow: 0 0 15px rgba(76, 175, 80, 0.5);
}

.insight-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
}

.insight-panel {
    padding: 24px;
}

.insight-panel h3 {
    color: #4CAF50;
    margin: 0 0 18px;
    text-align: center;
    text-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
}

.rank-list {
    display: grid;
    gap: 8px;
}

.rank-row {
    display: grid;
    grid-template-columns: 28px minmax(0, 1fr);
    gap: 8px 10px;
    align-items: center;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 10px;
}

.rank-number {
    color: #4CAF50;
    font-weight: 700;
}

.rank-row strong {
    color: #fff;
    overflow-wrap: anywhere;
}

.rank-row em {
    grid-column: 2;
    color: #aaa;
    font-style: normal;
}

@media (max-width: 968px) {
    .charts-dashboard {
        grid-template-columns: 1fr;
    }

    .insight-cards,
    .insight-grid {
        grid-template-columns: 1fr;
    }
}

.chart-container {
    background: linear-gradient(135deg, #2c2c2c 0%, #1e1e1e 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: fadeInUp 0.8s ease-out;
}

.chart-container.full-width {
    grid-column: 1 / -1;
}

.chart-container h2 {
    color: #4CAF50;
    font-size: 1.5rem;
    margin-bottom: 25px;
    text-align: center;
    text-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
}

.chart-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 25px;
    min-width: 0;
}

.chart-card-header h2 {
    flex: 1;
    margin-bottom: 0;
    min-width: 0;
}

.chart-metric-control {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: var(--card-muted-text);
    font-size: 0.9rem;
    font-weight: 700;
    min-width: 0;
    white-space: nowrap;
}

.chart-metric-select {
    align-items: center;
    display: inline-flex;
    flex: 0 1 190px;
    max-width: 190px;
    min-width: 0;
    position: relative;
    width: 190px;
}

.chart-metric-select select {
    appearance: none;
    background: color-mix(in srgb, var(--panel_top, #111) 88%, #000 12%);
    border: 1px solid color-mix(in srgb, var(--accent_color, #4CAF50) 45%, transparent);
    border-radius: 5px;
    box-sizing: border-box;
    color: var(--text_color, #fff);
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 700;
    min-height: 32px;
    min-width: 0;
    overflow: hidden;
    padding: 6px 28px 6px 9px;
    text-overflow: ellipsis;
    width: 100%;
}

.chart-metric-select::after {
    color: var(--accent_color, #4CAF50);
    content: "▼";
    font-size: 0.66rem;
    pointer-events: none;
    position: absolute;
    right: 10px;
}

.chart-container canvas {
    max-height: 350px;
}

@media (max-width: 640px) {
    .chart-card-header {
        align-items: stretch;
        flex-direction: column;
    }

    .chart-card-header h2 {
        text-align: left;
    }

    .chart-metric-control {
        justify-content: space-between;
        width: 100%;
    }

    .chart-metric-select {
        flex: 1;
        max-width: 100%;
        min-width: 0;
        width: 100%;
    }

    .chart-metric-select select {
        max-width: 100%;
        width: 100%;
    }
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loader {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(76, 175, 80, 0.3);
    border-top-color: #4CAF50;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-overlay p {
    color: #4CAF50;
    margin-top: 20px;
    font-size: 1.2rem;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes popIn {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.no-data-message {
    text-align: center;
    color: #888;
    font-style: italic;
    margin-top: 20px;
    font-size: 0.9rem;
}

/* Chart info icon and tooltip styles */
.chart-info {
    display: inline-block;
    width: 16px;
    height: 16px;
    line-height: 16px;
    text-align: center;
    background-color: #444;
    color: #ccc;
    border-radius: 50%;
    font-size: 12px;
    margin-left: 5px;
    cursor: help;
    transition: all 0.3s ease;
}

.chart-info:hover {
    background-color: #4CAF50;
    color: white;
    transform: scale(1.1);
}

.chart-container {
    position: relative;
}

.chart-container:hover {
    box-shadow: 0 12px 40px rgba(76, 175, 80, 0.3);
    border-color: rgba(76, 175, 80, 0.5);
}

.chart-container[title] {
    cursor: help;
}

/* Enhanced tooltip styling */
.chart-container:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: #fff;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    white-space: normal;
    max-width: 300px;
    text-align: center;
    z-index: 1000;
    pointer-events: none;
    opacity: 0;
    animation: fadeIn 0.3s forwards;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.chart-container:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 8px solid transparent;
    border-top-color: #333;
    margin-bottom: 2px;
    opacity: 0;
    animation: fadeIn 0.3s forwards;
}

@keyframes fadeIn {
    to {
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .charts-dashboard {
        grid-template-columns: 1fr;
    }
    
    .dashboard-header h1 {
        font-size: 2rem;
    }

    .chart-container:hover::after,
    .chart-container:hover::before {
        display: none;
    }

    .chart-container[title] {
        cursor: default;
    }
}

@media (hover: none), (pointer: coarse) {
    .chart-container:hover::after,
    .chart-container:hover::before {
        display: none;
    }

    .chart-info {
        cursor: default;
    }
}

@media (max-width: 980px) and (min-width: 769px) {
    .stat-card {
        padding: 24px;
        gap: 16px;
    }

    .stat-icon {
        font-size: 2.6rem;
    }

    .stat-content h3 {
        font-size: 0.9rem;
    }

    .stat-number {
        font-size: 2.25rem;
    }
}
</style>