<?php

namespace DcsStats\Services\Admin;

final class VersionTrackingService
{
    public function initialize(): array
    {
        \DcsStats\Core\AdminBootstrap::panel();
        \DcsStats\Core\SupportBootstrap::versionTracker();

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

