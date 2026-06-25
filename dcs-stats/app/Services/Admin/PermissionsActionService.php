<?php

namespace DcsStats\Services\Admin;

final class PermissionsActionService
{
    private PermissionsStore $store;

    public function __construct(?PermissionsStore $store = null)
    {
        $this->store = $store ?? new PermissionsStore();
    }

    public function handle(array $lsoPermissions, string $permissionsFile, bool $demoRestricted): array
    {
        if (!\verifyCSRFToken(\getRequestCSRFToken())) {
            return [$lsoPermissions, '', \dcs_t('admin.permissions.invalid_token')];
        }

        if ($demoRestricted) {
            return [$lsoPermissions, '', \demoRestrictionMessage()];
        }

        $enabledPerms = $_POST['permissions'] ?? [];

        foreach ($lsoPermissions as $key => &$perm) {
            $perm['enabled'] = in_array($key, $enabledPerms);
        }
        unset($perm);

        if (!$this->store->save($permissionsFile, $lsoPermissions)) {
            return [$lsoPermissions, '', \dcs_t('admin.permissions.save_failed')];
        }

        if (function_exists('logActivity')) {
            \logActivity('PERMISSIONS_UPDATE', 'Updated LSO group permissions');
        }

        $this->updateLsoPermissionsInConfig($lsoPermissions);

        return [$lsoPermissions, \dcs_t('admin.permissions.save_success'), ''];
    }

    private function updateLsoPermissionsInConfig(array $permissions): void
    {
        // Reserved for integration with the main auth permission backend.
    }
}
