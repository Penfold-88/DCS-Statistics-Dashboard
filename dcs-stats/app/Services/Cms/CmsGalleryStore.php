<?php

namespace DcsStats\Services\Cms;

final class CmsGalleryStore
{
    public function all(): array
    {
        if (!is_file($this->path())) {
            return [];
        }
        $items = json_decode((string)file_get_contents($this->path()), true);
        return is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
    }

    public function enabled(): array
    {
        return array_values(array_filter($this->all(), static fn(array $gallery): bool => !empty($gallery['enabled'])));
    }

    public function find(string $id): ?array
    {
        foreach ($this->all() as $gallery) {
            if (($gallery['id'] ?? '') === $id) return $gallery;
        }
        return null;
    }

    public function save(array $gallery): bool
    {
        $galleries = $this->all();
        $found = false;
        foreach ($galleries as $index => $existing) {
            if (($existing['id'] ?? '') === ($gallery['id'] ?? '')) {
                $galleries[$index] = $gallery;
                $found = true;
                break;
            }
        }
        if (!$found) $galleries[] = $gallery;
        return $this->write($galleries);
    }

    public function delete(string $id): bool
    {
        return $this->write(array_values(array_filter($this->all(), static fn(array $gallery): bool => ($gallery['id'] ?? '') !== $id)));
    }

    private function write(array $galleries): bool
    {
        $path = $this->path();
        if (!is_dir(dirname($path))) @mkdir(dirname($path), 0700, true);
        $result = @file_put_contents($path, json_encode($galleries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        if ($result !== false) @chmod($path, 0600);
        return $result !== false;
    }

    private function path(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/cms_galleries.json';
    }
}
