<script>
window.DCS_ADMIN_SETTINGS_FORM_CONFIG = {
    dependencies: <?= json_encode($dependencies) ?>,
    i18n: <?= json_encode([
        'expandAll' => dcs_t('admin.settings.expand_all_groups'),
        'collapseAll' => dcs_t('admin.settings.collapse_all_groups'),
        'majorConfirm' => dcs_t('admin.settings.major_confirm')
    ], JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="../js/admin/settings-form.js"></script>
