<?php
include DCS_APP_PATH . '/Views/Layout/header.php';
?>
<script src="<?php echo htmlspecialchars(assetUrl('js/vendor/chart.umd.min.js')); ?>"></script>
<?php include DCS_APP_PATH . '/Views/Layout/nav.php'; ?>

<?php include DCS_APP_PATH . '/Views/Public/pilot_statistics/content.php'; ?>

<?php include DCS_APP_PATH . '/Views/Public/pilot_statistics/script.php'; ?>

<?php include DCS_APP_PATH . '/Views/Public/pilot_statistics/styles.php'; ?>

<?php tableResponsiveStyles(); ?>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
