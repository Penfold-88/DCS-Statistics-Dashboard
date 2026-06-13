<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(dcs_default_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(dcs_t('maintenance.page_title'), ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('styles.php'), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('styles-mobile.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($customThemeExists): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('custom_theme.css'), ENT_QUOTES, 'UTF-8'); ?>">
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
    <?php require DCS_APP_PATH . '/Views/Public/maintenance/styles.php'; ?>
</body>
</html>
