<?php

namespace DcsStats\Services\Cms;

final class CmsPageStore
{
    public function all(): array
    {
        $path = $this->path();
        if (!file_exists($path)) {
            return [];
        }

        $pages = json_decode((string)file_get_contents($path), true);
        return is_array($pages) ? array_values(array_filter($pages, 'is_array')) : [];
    }

    public function find(string $id): ?array
    {
        foreach ($this->all() as $page) {
            if (($page['id'] ?? '') === $id) {
                return $page;
            }
        }
        return null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        foreach ($this->all() as $page) {
            if (($page['slug'] ?? '') === $slug && !empty($page['published'])) {
                return $page;
            }
        }
        return null;
    }

    public function save(array $page): bool
    {
        $pages = $this->all();
        $found = false;
        foreach ($pages as $index => $existing) {
            if (($existing['id'] ?? '') === ($page['id'] ?? '')) {
                $pages[$index] = $page;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $pages[] = $page;
        }
        return $this->write($pages);
    }

    public function delete(string $id): bool
    {
        return $this->write(array_values(array_filter(
            $this->all(),
            static fn(array $page): bool => ($page['id'] ?? '') !== $id
        )));
    }

    private function write(array $pages): bool
    {
        $path = $this->path();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        $result = @file_put_contents($path, json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        if ($result !== false) {
            @chmod($path, 0600);
        }
        return $result !== false;
    }

    private function path(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/pages.json';
    }
}
