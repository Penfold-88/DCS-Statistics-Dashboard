<?php

namespace DcsStats\Services\Cms;

final class CmsMediaStore
{
    public function all(): array
    {
        if (!file_exists($this->path())) {
            return [];
        }
        $items = json_decode((string)file_get_contents($this->path()), true);
        return is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
    }

    public function find(string $id): ?array
    {
        foreach ($this->all() as $item) {
            if (($item['id'] ?? '') === $id) {
                return $item;
            }
        }
        return null;
    }

    public function add(array $item): bool
    {
        $items = $this->all();
        $items[] = $item;
        return $this->write($items);
    }

    public function delete(string $id): bool
    {
        return $this->write(array_values(array_filter(
            $this->all(),
            static fn(array $item): bool => ($item['id'] ?? '') !== $id
        )));
    }

    private function write(array $items): bool
    {
        $path = $this->path();
        if (!is_dir(dirname($path))) {
            @mkdir(dirname($path), 0700, true);
        }
        $result = @file_put_contents($path, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        if ($result !== false) {
            @chmod($path, 0600);
        }
        return $result !== false;
    }

    private function path(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/page_media.json';
    }
}
