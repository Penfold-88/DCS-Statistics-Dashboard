<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateFinalizer
{
    public function complete(
        ?string $specificVersion,
        string $currentVersion,
        string $branch,
        ?string $remoteCommitSha,
        ?string $remoteCommitDate,
        callable $log
    ): string {
        $versionLabel = $specificVersion ?? $this->buildVersionLabel($branch, $remoteCommitDate, $remoteCommitSha);
        $currentAdmin = getCurrentAdmin();

        updateVersionMetadata(
            $versionLabel,
            $branch,
            $currentAdmin['username'] ?? 'Unknown',
            $remoteCommitSha,
            $remoteCommitDate
        );

        $this->runInstallCheckin($log);
        $this->updateConfigVersion($specificVersion, $currentVersion, $log);

        logAdminAction('SYSTEM_UPDATE', [
            'from_version' => $currentVersion,
            'to_version' => $specificVersion ?? $versionLabel,
            'branch' => $branch,
            'admin' => $currentAdmin['username'] ?? 'Unknown',
        ]);

        return $versionLabel;
    }

    private function runInstallCheckin(callable $log): void
    {
        \DcsStats\Core\SupportBootstrap::installCheckin();
        $checkinResult = runInstallCheckinIfDue(
            getCurrentVersionInfo(),
            getUpdateChannelConfig(),
            ['event' => 'update', 'force' => true]
        );
        $log('Install check-in after update: ' . ($checkinResult['status'] ?? 'unknown'));
    }

    private function updateConfigVersion(?string $newVersion, string $currentVersion, callable $log): void
    {
        if ($newVersion === null || $newVersion === $currentVersion) {
            return;
        }

        $configFile = DCS_ROOT_PATH . '/app/Core/AdminConfig.php';
        if (!file_exists($configFile)) {
            return;
        }

        $config = file_get_contents($configFile);
        $config = preg_replace(
            "/define\('ADMIN_PANEL_VERSION', '[^']+'/",
            "define('ADMIN_PANEL_VERSION', '$newVersion'",
            $config
        );
        file_put_contents($configFile, $config);
        $log("Updated version to: $newVersion");
    }

    private function buildVersionLabel(string $branch, ?string $commitDate, ?string $commitSha): string
    {
        $date = $commitDate ? date('Y-m-d', strtotime($commitDate)) : date('Y-m-d');
        $shortSha = $commitSha ? substr($commitSha, 0, 12) : 'unknown';

        return $branch . ' @ ' . $date . ' #' . $shortSha;
    }
}
