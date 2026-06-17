<?php

namespace DcsStats\Services\Admin;

final class InstallerWebFormService
{
    private InstallerSupportService $support;
    private InstallerPermissionCatalog $permissionCatalog;

    public function __construct(
        ?InstallerSupportService $support = null,
        ?InstallerPermissionCatalog $permissionCatalog = null
    ) {
        $this->support = $support ?? new InstallerSupportService();
        $this->permissionCatalog = $permissionCatalog ?? new InstallerPermissionCatalog();
    }

    public function submissionState(array $post, bool $isDev): array
    {
        $state = [
            'ready' => false,
            'errors' => [],
            'username' => 'admin',
            'email' => '',
            'password' => '',
            'api_url' => '',
            'api_key' => '',
            'site_name' => 'DCS Statistics',
            'discord_url' => '',
            'default_language' => \dcs_language_code($post['install_language'] ?? 'en'),
            'update_branch' => 'main',
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $state;
        }

        $state['username'] = $post['username'] ?? 'admin';
        $state['email'] = $post['email'] ?? '';
        $state['password'] = $post['password'] ?? '';
        $state['api_url'] = $post['api_url'] ?? '';
        $state['api_key'] = trim($post['api_key'] ?? '');
        $state['site_name'] = $post['site_name'] ?? 'DCS Statistics';
        $state['discord_url'] = $post['discord_url'] ?? '';

        $state['errors'] = $this->support->validateInputs([
            'username' => $state['username'],
            'email' => $state['email'],
            'password' => $state['password'],
            'api_url' => $state['api_url'],
            'api_key' => $state['api_key'],
        ]);

        if (empty($state['errors']) && $state['api_url'] !== '') {
            $apiResult = $this->support->resolveApiUrl($state['api_url'], $state['api_key'], $isDev);
            $state['api_url'] = $apiResult['url'];
            if (!$apiResult['connected']) {
                $state['errors'][] = $apiResult['error'];
            }
        }

        $state['ready'] = empty($state['errors']);

        return $state;
    }

    public function permissionState(): array
    {
        return [
            'folders' => $this->permissionCatalog->folders(),
            'files' => $this->permissionCatalog->files(),
            'folder_statuses' => $this->support->permissionStatuses($this->permissionCatalog->folders(), 'dir'),
            'file_statuses' => $this->support->permissionStatuses($this->permissionCatalog->files(), 'file'),
        ];
    }
}
