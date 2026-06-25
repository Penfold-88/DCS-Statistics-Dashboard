<?php

namespace DcsStats\Services\Admin;

final class InstallerPermissionService
{
    public function canCreateInPath(string $path): bool
    {
        $parent = dirname($path);
        while ($parent && $parent !== dirname($parent)) {
            if (file_exists($parent)) {
                return is_dir($parent) && is_writable($parent);
            }
            $parent = dirname($parent);
        }

        return false;
    }

    public function pathIsWritable(string $path, string $type): bool
    {
        if ($type === 'dir') {
            return is_dir($path) ? is_writable($path) : $this->canCreateInPath($path);
        }

        return file_exists($path) ? is_writable($path) : $this->canCreateInPath($path);
    }

    public function statuses(array $paths, string $type): array
    {
        $statuses = [];
        foreach ($paths as $label => $path) {
            $statuses[] = ['label' => $label, 'writable' => $this->pathIsWritable($path, $type)];
        }

        return $statuses;
    }
}
