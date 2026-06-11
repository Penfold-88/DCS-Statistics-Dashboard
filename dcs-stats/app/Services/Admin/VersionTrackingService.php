<?php

namespace DcsStats\Services\Admin;

final class VersionTrackingService
{
    public function initialize(): array
    {
        require_once DCS_ROOT_PATH . '/site-config/admin_functions.php';
        require_once DCS_ROOT_PATH . '/site-config/version_tracker.php';

        $versionInfo = initializeVersionTracking();
        $currentAdmin = getCurrentAdmin();

        logAdminAction('SYSTEM_VERSION_INIT', [
            'version' => $versionInfo['version'] ?? null,
            'branch' => $versionInfo['branch'] ?? null,
            'git_branch' => $versionInfo['git_branch'] ?? null,
            'admin' => $currentAdmin['username'] ?? 'Unknown',
        ]);

        return $versionInfo;
    }
}

