<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= e(dcs_t('admin.squadron_settings.preview_title')) ?></h3>
    </div>

    <p class="text-muted"><?= e(dcs_t('admin.squadron_settings.preview_help')) ?></p>

    <div style="background: #2a2a2a; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <nav style="display: flex; gap: 20px; flex-wrap: wrap;">
            <span style="color: #4CAF50;"><?= e(dcs_t('nav.home')) ?></span>
            <span style="color: #4CAF50;"><?= e(dcs_t('nav.leaderboard')) ?></span>
            <span style="color: #4CAF50;"><?= e(dcs_t('nav.pilot_statistics')) ?></span>
            <?php if (getFeatureValue('show_discord_link', true)): ?>
            <span style="color: #4CAF50;">Discord</span>
            <?php endif; ?>
            <?php if ($currentFeatures['show_squadron_homepage'] ?? false): ?>
            <span style="color: #4CAF50; font-weight: bold;">
                <?= e($currentFeatures['squadron_homepage_text'] ?? 'Squadron') ?>
            </span>
            <?php else: ?>
            <span style="color: #666; font-style: italic;"><?= e(dcs_t('admin.squadron_settings.preview_disabled')) ?></span>
            <?php endif; ?>
            <span style="color: #4CAF50;"><?= e(dcs_t('nav.servers')) ?></span>
        </nav>
    </div>
</div>
