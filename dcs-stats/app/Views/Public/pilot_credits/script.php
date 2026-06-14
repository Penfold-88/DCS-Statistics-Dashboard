<script>
window.DCS_PILOT_CREDITS_CONFIG = {
    i18n: <?php echo json_encode([
        'enterName' => dcs_t('pilot.enter_name'),
        'noMatches' => dcs_t('pilot.no_matches'),
        'checkSpelling' => dcs_t('pilot.check_spelling'),
        'usePartial' => dcs_t('pilot.use_partial'),
        'searchStart' => dcs_t('pilot.search_start'),
        'searchError' => dcs_t('pilot.search_error'),
        'noPilotData' => dcs_t('credits.no_pilot_data'),
        'noTransactions' => dcs_t('credits.no_transactions'),
        'loadError' => dcs_t('credits.load_error'),
        'tryLater' => dcs_t('credits.try_later')
    ], JSON_UNESCAPED_UNICODE); ?>
};
</script>
<script src="<?php echo htmlspecialchars(assetUrl('js/pages/pilot-credits.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
