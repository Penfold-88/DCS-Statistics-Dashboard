<?php

namespace DcsStats\Services\Admin;

final class ThemeColorSanitizer
{
    private ThemeColorCatalog $catalog;

    public function __construct(?ThemeColorCatalog $catalog = null)
    {
        $this->catalog = $catalog ?? new ThemeColorCatalog();
    }

    public function colors($colors): array
    {
        $clean = [];
        foreach ($this->catalog->defaultColors() as $key => $defaultValue) {
            $value = $colors[$key] ?? $defaultValue;
            $clean[$key] = is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)
                ? $value
                : $defaultValue;
        }

        return $clean;
    }

    public function options($options): array
    {
        return [
            'header_title_gradient_enabled' => !empty($options['header_title_gradient_enabled']),
            'page_background_gradient_enabled' => !empty($options['page_background_gradient_enabled']),
        ];
    }
}
