<?php if (!empty($maintenance['ip_whitelist'])): ?>
    <div class="card mt-2">
        <div class="card-header">
            <h2 class="card-title"><?= e(dcs_t('admin.maintenance.current_whitelist')) ?></h2>
        </div>
        <div class="card-content">
            <ul class="maintenance-whitelist">
                <?php foreach ($maintenance['ip_whitelist'] as $ip): ?>
                    <li class="maintenance-whitelist-item">
                        <span><?= e($ip) ?></span>
                        <form method="POST" class="maintenance-whitelist-remove">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="remove_ip">
                            <input type="hidden" name="ip" value="<?= e($ip) ?>">
                            <button type="submit" class="btn btn-danger btn-small"><?= e(dcs_t('admin.custom_links.remove')) ?></button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>
