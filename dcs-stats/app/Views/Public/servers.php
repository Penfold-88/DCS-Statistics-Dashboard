<?php
include DCS_APP_PATH . '/Views/Layout/header.php';
include DCS_APP_PATH . '/Views/Layout/nav.php';

$siteFeatures = loadSiteFeatures();
$serverCardVisibility = [];
foreach ($siteFeatures as $featureKey => $enabled) {
    if (strpos($featureKey, 'server_card_') === 0) {
        $serverCardVisibility[$featureKey] = (bool)$enabled;
    }
}

if (!isFeatureEnabled('nav_servers')):
?>
<main>
    <div class="alert" style="text-align: center; padding: 50px;">
        <h2><?php echo htmlspecialchars(dcs_t('servers.disabled_title')); ?></h2>
        <p><?php echo htmlspecialchars(dcs_t('servers.disabled_message')); ?></p>
    </div>
</main>
<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; exit; ?>
<?php endif; ?>

<?php include DCS_APP_PATH . '/Views/Public/servers/content.php'; ?>

<?php include DCS_APP_PATH . '/Views/Public/servers/script.php'; ?>

<?php include DCS_APP_PATH . '/Views/Public/servers/styles.php'; ?>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
