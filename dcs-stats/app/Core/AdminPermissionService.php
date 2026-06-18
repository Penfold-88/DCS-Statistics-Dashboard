<?php

namespace DcsStats\Core;

final class AdminPermissionService
{
    public function has($permission): bool
    {
        if (!AdminSessionAuth::isLoggedIn()) {
            return false;
        }

        $role = $_SESSION['admin_role'] ?? 0;

        if ($role === ROLE_AIR_BOSS) {
            return true;
        }

        if ($role === ROLE_LSO) {
            $customPermFile = $this->lsoPermissionsFile();
            if (file_exists($customPermFile)) {
                $customPerms = json_decode((string)file_get_contents($customPermFile), true);
                if ($customPerms && isset($customPerms[$permission])) {
                    return $customPerms[$permission]['enabled'] ?? false;
                }
            }
        }

        $permissions = ROLE_PERMISSIONS[$role] ?? [];
        return in_array($permission, $permissions);
    }

    private function lsoPermissionsFile(): string
    {
        $customPermFile = DCS_ROOT_PATH . '/site-config/data/lso_permissions.json';
        if (file_exists($customPermFile)) {
            return $customPermFile;
        }

        $customPermFile = DCS_ROOT_PATH . '/lso_permissions.json';
        if (file_exists($customPermFile)) {
            return $customPermFile;
        }

        return sys_get_temp_dir() . '/dcs_stats/lso_permissions.json';
    }
}
