<?php

namespace DcsStats\Services\Admin;

final class PermissionsPageService
{
    private PermissionsActionService $actions;
    private PermissionsStore $store;

    public function __construct(?PermissionsStore $store = null, ?PermissionsActionService $actions = null)
    {
        $this->store = $store ?? new PermissionsStore();
        $this->actions = $actions ?? new PermissionsActionService($this->store);
    }

    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::language();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $message = '';
        $error = '';
        $permissionsFile = $this->store->configPath();
        $lsoPermissions = $this->store->load($permissionsFile);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$lsoPermissions, $message, $error] = $this->actions->handle($lsoPermissions, $permissionsFile, $demoRestricted);
        }

        return [
            'csrfToken' => \getCSRFToken(),
            'demoRestricted' => $demoRestricted,
            'error' => $error,
            'lsoPermissions' => $lsoPermissions,
            'message' => $message,
            'pageTitle' => \dcs_t('admin.permissions.title'),
            'permissionsFile' => $permissionsFile,
        ];
    }
}
