<?php
$footerState = (new \DcsStats\Services\FooterService())->state();
$footerMetadata = $footerState['metadata'];
$footerShowLastUpdate = $footerState['showLastUpdate'];
$footerLastUpdate = $footerState['lastUpdate'];
?>
<footer>
  <p>
    &copy; 2025 DCS Statistics Dashboard |
    <?php if ($footerShowLastUpdate && $footerLastUpdate !== null): ?>
      <span class="footer-last-updated"><?php echo htmlspecialchars(dcs_t('footer.last_updated', ['date' => $footerLastUpdate])); ?></span> |
    <?php endif; ?>
    <button type="button" class="credits-link" id="openCredits"><?php echo htmlspecialchars(dcs_t('footer.credits')); ?></button>
    <?php if (!empty($footerMetadata['show_privacy_link'])): ?>
      | <a class="footer-privacy-link" href="<?php echo url('privacy.php'); ?>"><?php echo htmlspecialchars(dcs_t('footer.privacy')); ?></a>
    <?php endif; ?>
  </p>
</footer>

<div class="credits-modal" id="creditsModal" aria-hidden="true">
  <div class="credits-box" role="dialog" aria-modal="true" aria-labelledby="creditsTitle">
    <button type="button" class="credits-close" id="closeCredits" aria-label="<?php echo htmlspecialchars(dcs_t('footer.close_credits')); ?>">&times;</button>
    <h2 id="creditsTitle"><?php echo htmlspecialchars(dcs_t('footer.credits')); ?></h2>
    <div class="credits-list">
      <a href="https://skypirates.uk" target="_blank" rel="noopener noreferrer">VFS-252 Sky Pirates</a>
      <a href="https://503rdblacksheep.com" target="_blank" rel="noopener noreferrer">{503rd} Blacksheep</a>
      <a href="https://github.com/Special-K-s-Flightsim-Bots" target="_blank" rel="noopener noreferrer">Special K</a>
    </div>
  </div>
</div>

<link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('css/layout/footer.css'), ENT_QUOTES, 'UTF-8'); ?>">
<script src="<?php echo htmlspecialchars(assetUrl('js/layout/footer.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
