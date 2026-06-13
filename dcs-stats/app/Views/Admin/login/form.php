<div class="login-container">
    <div class="login-header">
        <h1><?= e(dcs_t('admin.login.title')) ?></h1>
        <p><?= e(dcs_t('admin.login.subtitle')) ?></p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrfField() ?>

        <div class="form-group">
            <label for="username"><?= e(dcs_t('admin.login.username_email')) ?></label>
            <input type="text" id="username" name="username" required autofocus
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password"><?= e(dcs_t('admin.login.password')) ?></label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="remember" name="remember" value="1">
            <label for="remember"><?= e(dcs_t('admin.login.remember')) ?></label>
        </div>

        <button type="submit" class="btn-login"><?= e(dcs_t('admin.login.login_button')) ?></button>
    </form>

    <div class="footer-links">
        <a href="<?php echo dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/index.php'; ?>"><?= e(dcs_t('admin.login.back_to_statistics')) ?></a>
    </div>

    <div class="security-notice">
        <span class="lock-icon">🔒</span>
        <?= e(dcs_t('admin.login.security_notice')) ?>
    </div>
</div>
