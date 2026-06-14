<script>
window.DCS_ADMIN_CUSTOM_LINKS_CONFIG = {
    nextIndex: <?= max(1, count($customLinks)) ?>,
    i18n: <?= json_encode([
        'label' => dcs_t('admin.custom_links.label'),
        'url' => dcs_t('admin.custom_links.url'),
        'enabled' => dcs_t('admin.status.enabled'),
        'newTab' => dcs_t('admin.custom_links.new_tab'),
        'remove' => dcs_t('admin.custom_links.remove')
    ], JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="../js/admin/custom-links-editor.js"></script>
