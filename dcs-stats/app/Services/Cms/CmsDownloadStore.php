<?php

namespace DcsStats\Services\Cms;

final class CmsDownloadStore
{
    public function all(): array
    {
        if (!is_file($this->path())) {
            return [];
        }

        $items = json_decode((string)file_get_contents($this->path()), true);
        $items = is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
        usort($items, static function (array $a, array $b): int {
            $sort = ((int)($a['sort_order'] ?? 0)) <=> ((int)($b['sort_order'] ?? 0));
            if ($sort !== 0) return $sort;
            return strcasecmp((string)($a['title'] ?? ''), (string)($b['title'] ?? ''));
        });

        return $items;
    }

    public function enabled(): array
    {
        return array_values(array_filter($this->all(), static fn(array $download): bool => !empty($download['enabled'])));
    }

    public function find(string $id): ?array
    {
        foreach ($this->all() as $download) {
            if (($download['id'] ?? '') === $id) return $download;
        }

        return null;
    }

    public function save(array $download): bool
    {
        $downloads = $this->all();
        $found = false;

        foreach ($downloads as $index => $existing) {
            if (($existing['id'] ?? '') === ($download['id'] ?? '')) {
                $downloads[$index] = $download;
                $found = true;
                break;
            }
        }

        if (!$found) $downloads[] = $download;

        return $this->write($downloads);
    }

    public function saveMany(array $downloads): bool
    {
        $existing = $this->all();
        foreach ($downloads as $download) {
            $found = false;
            foreach ($existing as $index => $item) {
                if (($item['id'] ?? '') === ($download['id'] ?? '')) {
                    $existing[$index] = $download;
                    $found = true;
                    break;
                }
            }
            if (!$found) $existing[] = $download;
        }

        return $this->write($existing);
    }

    public function delete(string $id): bool
    {
        return $this->write(array_values(array_filter($this->all(), static fn(array $download): bool => ($download['id'] ?? '') !== $id)));
    }

    private function write(array $downloads): bool
    {
        $path = $this->path();
        if (!is_dir(dirname($path))) @mkdir(dirname($path), 0700, true);
        $result = @file_put_contents($path, json_encode(array_values($downloads), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        if ($result !== false) @chmod($path, 0600);

        return $result !== false;
    }

    private function path(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/cms_downloads.json';
    }
}
