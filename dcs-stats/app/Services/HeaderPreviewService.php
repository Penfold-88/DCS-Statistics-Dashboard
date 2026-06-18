<?php

namespace DcsStats\Services;

final class HeaderPreviewService
{
    private HeaderPreviewColorCatalog $colorCatalog;

    public function __construct(?HeaderPreviewColorCatalog $colorCatalog = null)
    {
        $this->colorCatalog = $colorCatalog ?? new HeaderPreviewColorCatalog();
    }

    public function colors(array $query): ?array
    {
        if (($query['preview'] ?? null) !== '1') {
            return null;
        }

        $previewColors = [];
        foreach ($this->colorCatalog->keys() as $key) {
            $value = $query[$key] ?? null;
            $previewColors[$key] = is_string($value) && preg_match('/^[0-9A-Fa-f]{6}$/', $value)
                ? '#' . $value
                : null;
        }

        return $previewColors;
    }
}
