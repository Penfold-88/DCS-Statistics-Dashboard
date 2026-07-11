<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            Activity Logs
            <span class="text-muted">(<?= number_format($totalLogs) ?> results)</span>
        </h2>
    </div>

    <?php if (empty($logs)): ?>
        <p class="text-muted">No logs found for the selected criteria.</p>
    <?php else: ?>
        <div class="logs-list">
            <?php foreach ($logs as $log): ?>
                <?php
                $actionClass = '';
                if (strpos($log['action'], 'LOGIN') !== false) $actionClass = 'action-login';
                elseif (strpos($log['action'], 'LOGOUT') !== false) $actionClass = 'action-logout';
                elseif (strpos($log['action'], 'BAN') !== false && strpos($log['action'], 'UNBAN') === false) $actionClass = 'action-ban';
                elseif (strpos($log['action'], 'UNBAN') !== false) $actionClass = 'action-unban';
                elseif (strpos($log['action'], 'EXPORT') !== false) $actionClass = 'action-export';
                elseif (strpos($log['action'], 'EDIT') !== false) $actionClass = 'action-edit';
                ?>
                <div class="log-entry">
                    <div class="log-header">
                        <div>
                            <span class="action-badge <?= $actionClass ?>">
                                <?= e($log['action']) ?>
                            </span>
                            <span class="log-action">
                                <?= e(LOG_ACTIONS[$log['action']] ?? $log['action']) ?>
                            </span>
                        </div>
                        <div class="log-time">
                            <?= formatDate($log['created_at'] ?? '') ?>
                        </div>
                    </div>

                    <div class="log-details">
                        <strong><?= e($log['admin_username']) ?></strong>
                        <?php if (!empty($log['target_type']) && !empty($log['target_id'])): ?>
                            - <?= e($log['target_type']) ?>: <code><?= e($log['target_id']) ?></code>
                        <?php endif; ?>

                        <?php if (!empty($log['details'])): ?>
                            <div class="log-meta">
                                Details: <?= e(is_array($log['details']) ? json_encode($log['details']) : $log['details']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="log-meta">
                        IP: <?= e($log['ip_address'] ?? 'unknown') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
        <?= getPagination($totalLogs, $perPage, $page, $paginationBaseUrl) ?>
    <?php endif; ?>
</div>
