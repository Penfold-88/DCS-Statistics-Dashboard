<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.api.configuration_title')) ?></h2>
    </div>

    <form method="POST" action="" class="api-form">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save">

        <div class="form-group">
            <label for="api_host"><?= e(dcs_t('admin.api.host')) ?></label>
            <input type="text"
                   id="api_host"
                   name="api_host"
                   value="<?= e($displayApiHost) ?>"
                   placeholder="localhost:8080"
                   pattern="[a-zA-Z0-9.-]+:[0-9]+"
                   <?= $demoRestricted ? 'disabled' : '' ?>>
            <div class="help-text"><?= e(dcs_t('admin.api.host_help')) ?></div>
        </div>

        <div class="form-group">
            <label for="api_key">
                <?= e(dcs_t('admin.api.api_key')) ?>
                <span class="text-muted"><?= e(dcs_t('admin.api.api_key_optional')) ?></span>
            </label>
            <input type="password"
                   id="api_key"
                   name="api_key"
                   value=""
                   autocomplete="new-password"
                   placeholder="<?= $demoRestricted || $envApiKeyActive ? '••••••••' : (!empty($apiConfig['stored_api_key_present']) ? e(dcs_t('admin.api.api_key_saved')) : e(dcs_t('admin.api.api_key_placeholder'))) ?>"
                   <?= $demoRestricted ? 'disabled' : '' ?>>
            <div class="help-text"><?= e($envApiKeyActive ? dcs_t('admin.api.api_key_env_help') : dcs_t('admin.api.api_key_help')) ?></div>
        </div>

        <div class="form-group">
            <label for="timeout"><?= e(dcs_t('admin.api.timeout')) ?></label>
            <input type="number"
                   id="timeout"
                   name="timeout"
                   value="<?= e($apiConfig['timeout']) ?>"
                   min="5"
                   max="300"
                   <?= $demoRestricted ? 'disabled' : '' ?>>
            <div class="help-text"><?= e(dcs_t('admin.api.timeout_help')) ?></div>
        </div>

        <div class="form-group">
            <label for="refresh_interval"><?= e(dcs_t('admin.api.refresh_rate')) ?></label>
            <?php $refreshInterval = (int)($apiConfig['refresh_interval'] ?? 300); ?>
            <select id="refresh_interval" name="refresh_interval" <?= $demoRestricted ? 'disabled' : '' ?>>
                <option value="300" <?= $refreshInterval === 300 ? 'selected' : '' ?>><?= e(dcs_t('admin.api.refresh_5')) ?></option>
                <option value="600" <?= $refreshInterval === 600 ? 'selected' : '' ?>><?= e(dcs_t('admin.api.refresh_10')) ?></option>
                <option value="1800" <?= $refreshInterval === 1800 ? 'selected' : '' ?>><?= e(dcs_t('admin.api.refresh_30')) ?></option>
                <option value="3600" <?= $refreshInterval === 3600 ? 'selected' : '' ?>><?= e(dcs_t('admin.api.refresh_60')) ?></option>
            </select>
            <div class="help-text"><?= e(dcs_t('admin.api.refresh_help')) ?></div>
        </div>

        <div class="form-group">
            <label for="cache_ttl"><?= e(dcs_t('admin.api.cache_ttl')) ?></label>
            <input type="number"
                   id="cache_ttl"
                   name="cache_ttl"
                   value="<?= e($apiConfig['cache_ttl']) ?>"
                   min="0"
                   max="3600"
                   <?= $demoRestricted ? 'disabled' : '' ?>>
            <div class="help-text"><?= e(dcs_t('admin.api.cache_help')) ?></div>
        </div>

        <div class="checkbox-group">
            <input type="checkbox"
                   id="use_api"
                   name="use_api"
                   value="1"
                   <?= $apiConfig['use_api'] ? 'checked' : '' ?>
                   <?= $demoRestricted ? 'disabled' : '' ?>>
            <label for="use_api"><?= e(dcs_t('admin.api.enable_integration')) ?></label>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.api.save_configuration')) ?></button>
            <button type="submit" class="btn btn-secondary" name="action" value="test" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.api.test_connection')) ?></button>
            <button type="submit" class="btn btn-secondary" formnovalidate onclick="this.form.querySelector('input[name=action]').value='clear_cache';"><?= e(dcs_t('admin.api.clear_cache')) ?></button>
            <a href="api_health.php" class="btn btn-secondary"><?= e(dcs_t('admin.api.health_debug')) ?></a>
        </div>
    </form>
</div>
