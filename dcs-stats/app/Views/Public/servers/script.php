<script>
window.DCS_SERVERS_CONFIG = {
    serverCardVisibility: <?php echo json_encode($serverCardVisibility); ?>,
    detailFeatures: <?php echo json_encode([
        'description' => isFeatureEnabled('server_detail_description'),
        'status' => isFeatureEnabled('server_detail_status'),
        'mission' => isFeatureEnabled('server_detail_mission'),
        'slots' => isFeatureEnabled('server_detail_slots'),
        'restart' => isFeatureEnabled('server_detail_restart'),
        'weather' => isFeatureEnabled('server_detail_weather'),
        'extensions' => isFeatureEnabled('server_detail_extensions'),
        'activePlayers' => isFeatureEnabled('server_detail_active_players')
    ]); ?>,
    maskExtensionSecrets: <?php echo json_encode(isFeatureEnabled('server_detail_mask_extension_secrets')); ?>,
    publicDateFormat: <?php echo json_encode(dcs_public_date_format()); ?>,
    i18n: <?php echo json_encode([
        'unknownServer' => dcs_t('servers.unknown_server'),
        'unknown' => dcs_t('servers.unknown'),
        'notAvailable' => dcs_t('servers.not_available'),
        'noWeather' => dcs_t('servers.no_weather'),
        'noExtensions' => dcs_t('servers.no_extensions'),
        'noPlayers' => dcs_t('servers.no_players'),
        'extension' => dcs_t('servers.extension'),
        'wind' => dcs_t('servers.wind'),
        'degrees' => dcs_t('servers.degrees'),
        'cloudBase' => dcs_t('servers.cloud_base'),
        'slotsUsed' => dcs_t('servers.slots_used'),
        'blueShort' => dcs_t('servers.blue_short'),
        'redShort' => dcs_t('servers.red_short'),
        'mission' => dcs_t('servers.mission'),
        'theatre' => dcs_t('servers.theatre'),
        'slots' => dcs_t('servers.slots'),
        'restart' => dcs_t('servers.restart'),
        'weather' => dcs_t('servers.weather'),
        'extensions' => dcs_t('servers.extensions'),
        'activePlayers' => dcs_t('servers.active_players')
    ], JSON_UNESCAPED_UNICODE); ?>
};
</script>
<script src="<?php echo htmlspecialchars(assetUrl('js/pages/servers.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
