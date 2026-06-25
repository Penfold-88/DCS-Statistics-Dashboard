<?php

namespace DcsStats\Services\Admin;

use DcsStats\Core\DemoMode;

final class DemoAdminAccountPolicy
{
    public function isProtected(array $admin): bool
    {
        return DemoMode::isEnabled() && DemoMode::isOwner($admin);
    }

    public function message(string $action): string
    {
        $username = DemoMode::protectedUsername();
        $label = $username !== '' ? $username : 'The configured demo owner';

        return $label . ' admin account is protected and cannot be ' . $action . '.';
    }
}
