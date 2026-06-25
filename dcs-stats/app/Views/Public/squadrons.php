<?php
include DCS_APP_PATH . '/Views/Layout/header.php';
include DCS_APP_PATH . '/Views/Layout/nav.php';

if (!isFeatureEnabled('squadrons_enabled')):
?>
<main>
    <div class="alert" style="text-align: center; padding: 50px;">
        <h2><?php echo htmlspecialchars(dcs_t('squadrons.disabled_title')); ?></h2>
        <p><?php echo htmlspecialchars(dcs_t('squadrons.disabled_message')); ?></p>
    </div>
</main>
<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; exit; ?>
<?php endif; ?>

<?php include DCS_APP_PATH . '/Views/Public/squadrons/styles.php'; ?>

<?php tableResponsiveStyles(); ?>

<?php include DCS_APP_PATH . '/Views/Public/squadrons/content.php'; ?>

<?php include DCS_APP_PATH . '/Views/Public/squadrons/script.php'; ?>

<?php
include DCS_APP_PATH . '/Views/Layout/footer.php';
?>
