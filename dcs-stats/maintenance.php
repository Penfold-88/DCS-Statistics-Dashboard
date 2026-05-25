<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include path configuration for URL helper
require_once __DIR__ . '/config_path.php';
require_once __DIR__ . '/language.php';

// Load maintenance configuration
$maintenanceFile = __DIR__ . '/site-config/data/maintenance.json';
$maintenance = ['enabled' => false, 'ip_whitelist' => []];
if (file_exists($maintenanceFile)) {
    $data = json_decode(file_get_contents($maintenanceFile), true);
    if (is_array($data)) {
        $maintenance = array_merge($maintenance, $data);
    }
}

// Redirect to home when accessed directly and maintenance is not active
if (!defined('MAINTENANCE_OVERRIDE')) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (empty($maintenance['enabled']) || in_array($ip, $maintenance['ip_whitelist'])) {
        header('Location: index.php');
        exit;
    }
}

// Return proper maintenance status code
http_response_code(503);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(dcs_default_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(dcs_t('maintenance.page_title'), ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo url('styles.php'); ?>">
    <link rel="stylesheet" href="<?php echo url('styles-mobile.css'); ?>">
    <?php if (file_exists(__DIR__ . '/custom_theme.css')): ?>
    <link rel="stylesheet" href="<?php echo url('custom_theme.css'); ?>">
    <?php endif; ?>
</head>
<body class="maintenance-body">
    <main class="maintenance-shell">
        <section class="maintenance-hero" aria-labelledby="maintenance-title">
            <div class="maintenance-status">
                <span class="maintenance-status-dot"></span>
                <span><?php echo htmlspecialchars(dcs_t('maintenance.status_label'), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="maintenance-emblem" aria-hidden="true">
                <span class="maintenance-emblem-ring"></span>
                <span class="maintenance-emblem-icon">⚙</span>
            </div>

            <h1 id="maintenance-title"><?php echo htmlspecialchars(dcs_t('maintenance.header_title'), ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="maintenance-subtitle"><?php echo htmlspecialchars(dcs_t('maintenance.header_subtitle'), ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="maintenance-message"><?php echo htmlspecialchars(dcs_t('maintenance.message'), ENT_QUOTES, 'UTF-8'); ?></p>

            <div class="maintenance-cards" aria-label="<?php echo htmlspecialchars(dcs_t('maintenance.progress_label'), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="maintenance-mini-card">
                    <strong><?php echo htmlspecialchars(dcs_t('maintenance.card_dashboard'), ENT_QUOTES, 'UTF-8'); ?></strong>
                    <span><?php echo htmlspecialchars(dcs_t('maintenance.card_dashboard_text'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="maintenance-mini-card">
                    <strong><?php echo htmlspecialchars(dcs_t('maintenance.card_data'), ENT_QUOTES, 'UTF-8'); ?></strong>
                    <span><?php echo htmlspecialchars(dcs_t('maintenance.card_data_text'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="maintenance-mini-card">
                    <strong><?php echo htmlspecialchars(dcs_t('maintenance.card_return'), ENT_QUOTES, 'UTF-8'); ?></strong>
                    <span><?php echo htmlspecialchars(dcs_t('maintenance.card_return_text'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </section>
    </main>
    <style>
        .maintenance-body {
            background:
                radial-gradient(circle at top left, color-mix(in srgb, var(--accent_color, #4CAF50) 18%, transparent), transparent 32rem),
                linear-gradient(135deg, var(--background_color, #0b1218) 0%, var(--background_gradient_color, #111827) 100%);
            color: var(--text_color, #e0e0e0);
            min-height: 100vh;
        }

        .maintenance-shell {
            align-items: center;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            padding: 32px 18px;
        }

        .maintenance-hero {
            background: linear-gradient(135deg, var(--card_color, #132b45) 0%, var(--card_alt_color, #081826) 100%);
            border: 1px solid color-mix(in srgb, var(--accent_color, #4CAF50) 45%, transparent);
            border-radius: 10px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.45);
            max-width: 900px;
            overflow: hidden;
            padding: clamp(28px, 5vw, 56px);
            position: relative;
            text-align: center;
            width: min(100%, 900px);
        }

        .maintenance-hero::before {
            background: linear-gradient(90deg, var(--accent_color, #4CAF50), var(--header_title_gradient_color, #71d6ff));
            content: '';
            height: 4px;
            inset: 0 0 auto;
            position: absolute;
        }

        .maintenance-status {
            align-items: center;
            background: color-mix(in srgb, var(--secondary_color, #0c2032) 82%, transparent);
            border: 1px solid color-mix(in srgb, var(--accent_color, #4CAF50) 38%, transparent);
            border-radius: 999px;
            color: var(--card_heading_color, #ffd21f);
            display: inline-flex;
            font-size: 0.82rem;
            font-weight: 800;
            gap: 9px;
            letter-spacing: 0.08em;
            margin-bottom: 28px;
            padding: 9px 14px;
            text-transform: uppercase;
        }

        .maintenance-status-dot {
            animation: maintenancePulse 1.8s ease-in-out infinite;
            background: var(--warning_color, #ffd21f);
            border-radius: 999px;
            box-shadow: 0 0 16px color-mix(in srgb, var(--warning_color, #ffd21f) 75%, transparent);
            display: inline-block;
            height: 9px;
            width: 9px;
        }

        .maintenance-emblem {
            align-items: center;
            display: inline-flex;
            height: 92px;
            justify-content: center;
            margin-bottom: 26px;
            position: relative;
            width: 92px;
        }

        .maintenance-emblem-ring {
            animation: maintenanceSpin 14s linear infinite;
            border: 2px solid color-mix(in srgb, var(--accent_color, #4CAF50) 30%, transparent);
            border-left-color: var(--accent_color, #4CAF50);
            border-radius: 999px;
            inset: 0;
            position: absolute;
        }

        .maintenance-emblem-icon {
            align-items: center;
            background: color-mix(in srgb, var(--secondary_color, #0c2032) 90%, transparent);
            border-radius: 999px;
            color: var(--card_heading_color, #ffd21f);
            display: flex;
            font-size: 2.4rem;
            height: 68px;
            justify-content: center;
            width: 68px;
        }

        .maintenance-hero h1 {
            color: var(--header_text_color, var(--heading_color, #fff));
            font-size: clamp(2.1rem, 6vw, 4.4rem);
            line-height: 1;
            margin: 0 0 14px;
        }

        .maintenance-subtitle {
            color: var(--header_subtitle_color, var(--muted_text_color, #b8c7d5));
            font-size: clamp(1rem, 2.4vw, 1.35rem);
            margin: 0 auto 20px;
            max-width: 680px;
        }

        .maintenance-message {
            color: var(--card_text_color, #e7eef7);
            font-size: 1.05rem;
            line-height: 1.7;
            margin: 0 auto 34px;
            max-width: 660px;
        }

        .maintenance-cards {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 28px;
            text-align: left;
        }

        .maintenance-mini-card {
            background: color-mix(in srgb, var(--secondary_color, #0c2032) 86%, transparent);
            border: 1px solid color-mix(in srgb, var(--border_color, #315270) 55%, transparent);
            border-radius: 8px;
            padding: 16px;
        }

        .maintenance-mini-card strong {
            color: var(--card_heading_color, #ffd21f);
            display: block;
            font-size: 0.95rem;
            margin-bottom: 7px;
        }

        .maintenance-mini-card span {
            color: var(--card_muted_text_color, #b8c7d5);
            display: block;
            font-size: 0.9rem;
            line-height: 1.45;
        }

        @keyframes maintenancePulse {
            0%, 100% { opacity: 0.55; transform: scale(0.92); }
            50% { opacity: 1; transform: scale(1.1); }
        }

        @keyframes maintenanceSpin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 760px) {
            .maintenance-cards {
                grid-template-columns: 1fr;
            }

            .maintenance-hero {
                text-align: left;
            }

            .maintenance-status,
            .maintenance-emblem {
                margin-left: 0;
            }
        }
    </style>
</body>
</html>
