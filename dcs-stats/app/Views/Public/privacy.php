<?php
include DCS_APP_PATH . '/Views/Layout/header.php';
include DCS_APP_PATH . '/Views/Layout/nav.php';
?>

<main>
    <div class="dashboard-header">
        <h1>Privacy</h1>
        <p class="dashboard-subtitle">How this dashboard presents and handles site information</p>
    </div>

    <section class="privacy-panel">
        <?= nl2br(htmlspecialchars($metadata['privacy_notice'] ?? '', ENT_QUOTES)) ?>
    </section>
</main>

<?php include DCS_APP_PATH . '/Views/Public/privacy/styles.php'; ?>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
