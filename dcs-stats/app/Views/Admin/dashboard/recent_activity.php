<!-- Recent Activity -->
<div class="card compact-card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.dashboard.bridge_log')) ?></h2>
        <a href="logs.php" class="btn btn-primary btn-small"><?= e(dcs_t('admin.common.view_all')) ?></a>
    </div>

    <?php if (empty($stats['recent_activity'])): ?>
        <p class="text-muted"><?= e(dcs_t('admin.dashboard.no_recent_activity')) ?></p>
    <?php else: ?>
        <div class="activity-list">
            <?php foreach ($stats['recent_activity'] as $activity): ?>
                <div class="activity-item">
                    <div class="activity-time"><?= formatDate($activity['created_at'] ?? '') ?></div>
                    <div class="activity-action">
                        <strong><?= e($activity['admin_username'] ?? dcs_t('home.unknown')) ?></strong>
                        <?= e(LOG_ACTIONS[$activity['action'] ?? ''] ?? ($activity['action'] ?? dcs_t('home.unknown'))) ?>
                        <?php if (!empty($activity['target_type'])): ?>
                            <div style="margin-top: 5px;">
                                <span class="text-muted"><?= e(dcs_t('admin.dashboard.target')) ?>: <?= e($activity['target_type']) ?></span>
                                <?php if (!empty($activity['target_id'])): ?>
                                    <code style="font-size: 11px;"><?= e(substr($activity['target_id'], 0, 50)) ?><?= strlen($activity['target_id']) > 50 ? '...' : '' ?></code>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($activity['details'])): ?>
                        <div class="activity-details">
                            <?php
                            $details = is_array($activity['details']) ? json_encode($activity['details']) : $activity['details'];
                            echo e(substr($details, 0, 100)) . (strlen($details) > 100 ? '...' : '');
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
