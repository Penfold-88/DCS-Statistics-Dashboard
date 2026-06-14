<!-- Theme Tabs -->
<div class="theme-tabs">
    <button class="theme-tab active" data-theme-tab="simple"><?= e(dcs_t('admin.themes.simple_customization')) ?></button>
    <button class="theme-tab" data-theme-tab="presets"><?= e(dcs_t('admin.themes.theme_presets')) ?></button>
    <button class="theme-tab" data-theme-tab="header-image"><?= e(dcs_t('admin.themes.header_image')) ?></button>
    <button class="theme-tab" data-theme-tab="menu"><?= e(dcs_t('admin.themes.menu_configuration')) ?></button>
    <button class="theme-tab" data-theme-tab="charts"><?= e(dcs_t('admin.themes.chart_colours')) ?></button>
    <?php if ($isAirBoss): ?>
    <button class="theme-tab" data-theme-tab="advanced"><?= e(dcs_t('admin.themes.advanced_css_upload')) ?></button>
    <button class="theme-tab" data-theme-tab="backups"><?= e(dcs_t('admin.themes.backup_restore')) ?></button>
    <?php endif; ?>
</div>
