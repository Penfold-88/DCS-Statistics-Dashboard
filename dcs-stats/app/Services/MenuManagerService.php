<?php

namespace DcsStats\Services;

final class MenuManagerService
{
    private MenuConfigPathService $pathService;
    private MenuItemRegistry $registry;

    public function __construct(?MenuConfigPathService $pathService = null, ?MenuItemRegistry $registry = null)
    {
        $this->pathService = $pathService ?? new MenuConfigPathService();
        $this->registry = $registry ?? new MenuItemRegistry();
    }

    public function configPath(): string
    {
        return $this->pathService->configPath();
    }

    public function items(): array
    {
        $saved = $this->read();
        $candidates = $this->registry->candidates();
        $candidateIds = array_column($candidates, 'id');
        $merged = [];

        foreach ($saved as $position => $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = (string)($item['id'] ?? $this->legacyId($item));
            $candidateIndex = array_search($id, $candidateIds, true);
            if ($candidateIndex === false) {
                continue;
            }
            $candidate = $candidates[$candidateIndex];
            $savedName = trim((string)($item['name'] ?? ''));
            if (in_array($savedName, $candidate['legacy_names'] ?? [], true)) {
                $savedName = (string)$candidate['name'];
            }
            $merged[] = array_merge($candidate, [
                'name' => $savedName ?: $candidate['name'],
                'enabled' => (bool)($item['enabled'] ?? true),
                'parent_id' => (string)($item['parent_id'] ?? ($candidate['parent_id'] ?? '')),
                'new_tab' => (bool)($item['new_tab'] ?? ($candidate['new_tab'] ?? false)),
            ]);
            unset($candidates[$candidateIndex]);
        }

        foreach ($candidates as $candidate) {
            $afterId = (string)($candidate['after_id'] ?? '');
            $inserted = false;
            if ($afterId !== '') {
                foreach ($merged as $position => $mergedItem) {
                    if (($mergedItem['id'] ?? '') === $afterId) {
                        array_splice($merged, $position + 1, 0, [$candidate]);
                        $inserted = true;
                        break;
                    }
                }
            }
            if (!$inserted) {
                $merged[] = $candidate;
            }
        }

        return $this->normalizeParents(array_values($merged));
    }

    public function fromPost(array $post, array $currentItems): array
    {
        $submitted = $post['items'] ?? [];
        $byId = [];
        foreach ($currentItems as $item) {
            $byId[$item['id']] = $item;
        }
        $result = [];
        foreach ($submitted as $row) {
            if (!is_array($row) || !isset($byId[$row['id'] ?? ''])) {
                continue;
            }
            $source = $byId[$row['id']];
            $source['name'] = trim((string)($row['name'] ?? '')) ?: $source['name'];
            $source['enabled'] = isset($row['enabled']);
            $source['parent_id'] = (string)($row['parent_id'] ?? '');
            $source['new_tab'] = isset($row['new_tab']);
            $result[] = $source;
            unset($byId[$row['id']]);
        }
        foreach ($byId as $item) {
            $result[] = $item;
        }
        return $this->normalizeParents($result);
    }

    public function save(array $items): bool
    {
        $stored = array_map(static function (array $item): array {
            return array_filter([
                'id' => $item['id'],
                'name' => $item['name'],
                'enabled' => (bool)$item['enabled'],
                'parent_id' => $item['parent_id'] ?? '',
                'new_tab' => (bool)($item['new_tab'] ?? false),
            ], static fn($value, $key): bool => !($key === 'parent_id' && $value === ''), ARRAY_FILTER_USE_BOTH);
        }, $items);
        $result = @file_put_contents($this->configPath(), json_encode($stored, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        if ($result !== false) {
            @chmod($this->configPath(), 0600);
        }
        return $result !== false;
    }

    private function read(): array
    {
        $path = $this->configPath();
        if (!file_exists($path)) {
            return [];
        }
        $items = json_decode((string)file_get_contents($path), true);
        return is_array($items) ? $items : [];
    }

    private function legacyId(array $item): string
    {
        $types = ['discord' => 'integration-discord', 'squadron_homepage' => 'integration-squadron-homepage'];
        if (isset($types[$item['type'] ?? ''])) {
            return $types[$item['type']];
        }
        $urls = [
            'index.php' => 'stats-home', 'leaderboard.php' => 'stats-leaderboard',
            'pilot_statistics.php' => 'stats-pilot-statistics', 'pilot_credits.php' => 'stats-pilot-credits',
            'squadrons.php' => 'stats-squadrons', 'servers.php' => 'stats-servers',
        ];
        return $urls[$item['url'] ?? ''] ?? '';
    }

    private function normalizeParents(array $items): array
    {
        $ids = array_column($items, 'id');
        $parents = [];
        foreach ($items as &$item) {
            $parent = (string)($item['parent_id'] ?? '');
            if ($parent === $item['id'] || !in_array($parent, $ids, true)) {
                $parent = '';
            }
            $item['parent_id'] = $parent;
            $parents[$item['id']] = $parent;
        }
        unset($item);
        foreach ($items as &$item) {
            $parent = $item['parent_id'];
            if ($parent !== '' && ($parents[$parent] ?? '') !== '') {
                $item['parent_id'] = '';
            }
        }
        unset($item);
        return $items;
    }
}
