<?php
include DCS_APP_PATH . '/Views/Layout/header.php';
include DCS_APP_PATH . '/Views/Layout/nav.php';
?>

<?php $chartTheme = loadChartTheme(); ?>

<?php include DCS_APP_PATH . '/Views/Public/leaderboard/styles.php'; ?>

<?php tableResponsiveStyles(); ?>

<?php include DCS_APP_PATH . '/Views/Public/leaderboard/content.php'; ?>

<?php include DCS_APP_PATH . '/Views/Public/leaderboard/script.php'; ?>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
