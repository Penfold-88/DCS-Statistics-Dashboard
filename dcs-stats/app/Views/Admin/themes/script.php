<script>
window.DCS_ADMIN_THEMES_CONFIG = {
    csrfToken: <?= json_encode($csrfToken) ?>,
    defaultMenuItems: <?= json_encode(array_values($defaultMenuItems)) ?>,
    defaultThemeColors: <?= json_encode($defaultThemeColors) ?>,
    defaultChartTheme: <?= json_encode($defaultChartTheme) ?>,
    i18n: <?= json_encode([
        'noFileSelected' => dcs_t('admin.themes.no_file_selected'),
        'noNewImageSelected' => dcs_t('admin.themes.no_new_image_selected'),
        'defaultImageSelected' => dcs_t('admin.themes.default_image_selected'),
        'noNewBackgroundSelected' => dcs_t('admin.themes.no_new_background_selected'),
        'backgroundWillBeRemoved' => dcs_t('admin.themes.background_will_be_removed'),
        'noNewLogoSelected' => dcs_t('admin.themes.no_new_logo_selected'),
        'logoWillBeRemoved' => dcs_t('admin.themes.logo_will_be_removed'),
        'updatingPreview' => dcs_t('admin.themes.updating_preview'),
        'previewUpdated' => dcs_t('admin.themes.preview_updated'),
        'resetMenuConfirm' => dcs_t('admin.themes.reset_menu_confirm'),
        'colorsRestored' => dcs_t('admin.themes.colors_restored')
    ], JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="../js/admin/themes.js"></script>
