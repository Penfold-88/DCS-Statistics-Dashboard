<?php

namespace DcsStats\Core;

final class DemoAccessPolicy
{
    private DemoConfigReader $configReader;

    public function __construct(?DemoConfigReader $configReader = null)
    {
        $this->configReader = $configReader ?? new DemoConfigReader();
    }

    public function isOwner($admin = null): bool
    {
        if (!$admin && function_exists('getCurrentAdmin')) {
            $admin = \getCurrentAdmin();
        }

        $protectedUsername = $this->configReader->protectedUsername();
        return $protectedUsername !== ''
            && is_array($admin)
            && hash_equals($protectedUsername, (string)($admin['username'] ?? ''));
    }

    public function isRestricted($admin = null): bool
    {
        return $this->configReader->configPath() !== '' && !$this->isOwner($admin);
    }

    public function maskValue($value): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        $port = '';
        if (preg_match('/:(\d+)$/', $value, $matches)) {
            $port = ':' . $matches[1];
        }

        return '••••••••' . $port;
    }
}
