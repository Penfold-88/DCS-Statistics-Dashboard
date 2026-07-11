<main>
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars(dcs_t('servers.title')); ?></h1>
        <p class="dashboard-subtitle"><?php echo htmlspecialchars(dcs_t('servers.subtitle')); ?></p>
    </div>

    <div id="servers-loading" style="text-align: center; padding: 50px;">
        <p><?php echo htmlspecialchars(dcs_t('servers.loading')); ?></p>
    </div>

    <div id="servers-container" style="display: none;">
        <?php if (isFeatureEnabled('server_live_api_details')): ?>
        <div class="api-section">
            <div class="server-details-grid" id="serverDetailsGrid"></div>
        </div>
        <?php endif; ?>
    </div>

    <div id="no-servers" style="display: none; text-align: center; padding: 50px;">
        <p><?php echo htmlspecialchars(dcs_t('servers.no_info')); ?></p>
    </div>
</main>
