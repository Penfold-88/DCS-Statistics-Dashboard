<?php

namespace DcsStats\Services\Admin;

final class AdminDashboardInstallerService
{
    public function handleRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'delete_installer') {
            return ['', ''];
        }

        \requirePermission('change_settings');
        \requireCSRFToken();

        $installPath = DCS_ROOT_PATH . '/site-config/install.php';
        $installRealPath = realpath($installPath);
        $adminRealPath = realpath(DCS_ROOT_PATH . '/site-config');

        if (!file_exists($installPath)) {
            return [\dcs_t('admin.dashboard.install_file_delete_missing'), 'success'];
        }

        if (
            $installRealPath === false ||
            $adminRealPath === false ||
            $installRealPath !== $adminRealPath . DIRECTORY_SEPARATOR . 'install.php' ||
            !is_file($installRealPath)
        ) {
            return [\dcs_t('admin.dashboard.install_file_delete_failed'), 'error'];
        }

        if (@unlink($installRealPath)) {
            \logAdminAction('INSTALLER_FILE_DELETE', ['file' => 'site-config/install.php']);
            return [\dcs_t('admin.dashboard.install_file_delete_success'), 'success'];
        }

        return [\dcs_t('admin.dashboard.install_file_delete_failed'), 'error'];
    }
}
