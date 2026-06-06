<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/site_metadata.php';

$metadata = loadSiteMetadata();

include 'header.php';
include 'nav.php';
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

<style>
.privacy-panel {
    background: linear-gradient(135deg, var(--card_color) 0%, var(--card_alt_color) 100%);
    border: 1px solid color-mix(in srgb, var(--border_color) 70%, transparent);
    border-radius: 8px;
    color: var(--card_text_color);
    line-height: 1.7;
    margin: 0 auto 30px;
    max-width: 980px;
    padding: 24px;
}
</style>

<?php include 'footer.php'; ?>
