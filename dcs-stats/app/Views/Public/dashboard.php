<?php
require DCS_APP_PATH . '/Views/Layout/header.php';
?>
<script src="<?php echo htmlspecialchars(assetUrl('js/vendor/chart.umd.min.js')); ?>"></script>
<?php require DCS_APP_PATH . '/Views/Layout/nav.php'; ?>
<?php require DCS_APP_PATH . '/Views/Public/dashboard/content.php'; ?>

<?php require DCS_APP_PATH . '/Views/Public/dashboard/script.php'; ?>

<?php require DCS_APP_PATH . '/Views/Public/dashboard/styles.php'; ?>

<?php require DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
