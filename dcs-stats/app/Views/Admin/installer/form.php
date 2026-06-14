<!DOCTYPE html>
<html lang="<?= e($installerLanguage) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(dcs_t('admin.install.page_title')) ?> - DCS Statistics</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="../css/admin/installer.css">
</head>
<body>
    <div class="install-container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title"><?= e(dcs_t('admin.install.title')) ?></h1>
            </div>
            <p class="text-center text-muted mb-3"><?= e(dcs_t('admin.install.subtitle')) ?></p>

            <form method="GET" class="card mb-3" style="padding: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="installer_language_selector"><?= e(dcs_t('admin.install.language')) ?></label>
                    <select id="installer_language_selector" name="lang" class="form-control" onchange="this.form.submit()">
                        <?php foreach (dcs_supported_languages() as $code => $label): ?>
                            <option value="<?= e($code) ?>" <?= $installerLanguage === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <div class="card mb-3">
                <h3 class="text-success"><?= e(dcs_t('admin.install.system_requirements')) ?></h3>
                <p class="<?= version_compare(PHP_VERSION, '7.4.0', '>=') ? 'success' : 'error' ?>">
                    <?= version_compare(PHP_VERSION, '7.4.0', '>=') ? '✓' : '✗' ?> <?= e(dcs_t('admin.install.php_version_required', ['version' => PHP_VERSION])) ?>
                </p>
                <?php foreach ($required_extensions as $ext): ?>
                    <?php $loaded = extension_loaded($ext); ?>
                    <p class="<?= $loaded ? 'success' : 'error' ?>">
                        <?= $loaded ? '✓' : '✗' ?> <?= e($ext) ?> <?= e(dcs_t('admin.install.extension')) ?>
                    </p>
                <?php endforeach; ?>
                <p class="<?= is_writable(dirname($dataDir)) ? 'success' : 'error' ?>">
                    <?= is_writable(dirname($dataDir)) ? '✓' : '✗' ?> <?= e(dcs_t('admin.install.write_permissions')) ?>
                </p>
                <details class="permission-details">
                    <summary><?= e(dcs_t('admin.install.permissions_view')) ?></summary>
                    <p class="text-muted"><?= e(dcs_t('admin.install.permissions_help')) ?></p>
                    <strong><?= e(dcs_t('admin.install.permissions_folders')) ?></strong>
                    <?php foreach ($permissionFolderStatuses as $permissionStatus): ?>
                        <?php $pathWritable = $permissionStatus['writable']; ?>
                        <div class="permission-row <?= $pathWritable ? 'success' : 'error' ?>">
                            <span class="permission-status"><?= $pathWritable ? '✓' : '✗' ?></span>
                            <code><?= e($permissionStatus['label']) ?></code>
                        </div>
                    <?php endforeach; ?>
                    <strong><?= e(dcs_t('admin.install.permissions_files')) ?></strong>
                    <?php foreach ($permissionFileStatuses as $permissionStatus): ?>
                        <?php $pathWritable = $permissionStatus['writable']; ?>
                        <div class="permission-row <?= $pathWritable ? 'success' : 'error' ?>">
                            <span class="permission-status"><?= $pathWritable ? '✓' : '✗' ?></span>
                            <code><?= e($permissionStatus['label']) ?></code>
                        </div>
                    <?php endforeach; ?>
                </details>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error mb-2">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($showInstallerForm): ?>
            <form method="POST">
                <input type="hidden" name="install_language" value="<?= e($installerLanguage) ?>">

                <div class="form-group">
                    <label for="username"><?= e(dcs_t('admin.install.admin_username')) ?></label>
                    <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email"><?= e(dcs_t('admin.install.admin_email')) ?></label>
                    <input type="text" id="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password"><?= e(dcs_t('admin.install.admin_password')) ?></label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="8">
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><?= e(dcs_t('admin.install.api_configuration')) ?></h3>
                    </div>
                    <p class="text-muted"><?= e(dcs_t('admin.install.api_configuration_help')) ?></p>

                    <div class="form-group">
                        <label for="api_url"><?= e(dcs_t('admin.install.api_url')) ?></label>
                        <input type="text" id="api_url" name="api_url" class="form-control" placeholder="your-server:9876" value="<?= htmlspecialchars($_POST['api_url'] ?? '') ?>" required>
                        <?php if ($isDev): ?>
                        <small class="text-warning"><?= e(dcs_t('admin.install.dev_mode_skip')) ?></small>
                        <?php else: ?>
                        <small class="text-muted"><?= e(dcs_t('admin.install.api_example')) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="api_key">
                            <?= e(dcs_t('admin.install.api_key')) ?>
                            <span class="text-muted"><?= e(dcs_t('admin.install.api_key_optional')) ?></span>
                        </label>
                        <input type="password" id="api_key" name="api_key" class="form-control" value="" autocomplete="off" placeholder="<?= e(dcs_t('admin.install.api_key_placeholder')) ?>">
                        <small class="text-muted"><?= e(dcs_t('admin.install.api_key_help')) ?></small>
                    </div>

                    <div class="form-group">
                        <label for="site_name"><?= e(dcs_t('admin.install.site_name')) ?></label>
                        <input type="text" id="site_name" name="site_name" class="form-control" value="<?= htmlspecialchars($_POST['site_name'] ?? 'DCS Statistics') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="discord_url"><?= e(dcs_t('admin.install.discord_url')) ?></label>
                        <input type="url" id="discord_url" name="discord_url" class="form-control" placeholder="https://discord.gg/your-invite">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;"><?= e(dcs_t('admin.install.install_button')) ?></button>
            </form>

            <div class="alert alert-info">
                <strong><?= e(dcs_t('admin.common.note')) ?>:</strong> <?= e(dcs_t('admin.install.cli_note')) ?><br>
                <code>php install.php</code>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?= getDevModeIndicator() ?>
</body>
</html>
