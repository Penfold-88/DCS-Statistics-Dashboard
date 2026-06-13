<?php if (defined('ADMIN_PANEL') && $demoMode): ?>
<div class="demo-admin-banner" role="note" style="background: linear-gradient(90deg, #ffd21f 0%, #ff8a00 100%); border-bottom: 2px solid rgba(0,0,0,0.35); color: #101010; font-size: 14px; font-weight: 700; left: 0; letter-spacing: 0; padding: 10px 18px; position: fixed; right: 0; text-align: center; top: 0; z-index: 3000;">
    <strong>DEMO MODE:</strong>
    All sensitive data is restricted for security. Data resets every hour.
</div>
<style>
    .admin-wrapper {
        padding-top: 42px;
    }

    .demo-readonly-lock {
        opacity: 0.72;
        pointer-events: none;
    }
</style>
<?php endif; ?>
