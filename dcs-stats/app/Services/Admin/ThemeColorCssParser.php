<?php

namespace DcsStats\Services\Admin;

final class ThemeColorCssParser
{
    private ThemeColorCatalog $catalog;

    public function __construct(?ThemeColorCatalog $catalog = null)
    {
        $this->catalog = $catalog ?? new ThemeColorCatalog();
    }

    public function optionsFromContent(string $content): array
    {
        $options = $this->catalog->defaultOptions();
        if (preg_match('/--header_title_gradient_enabled:\s*(0|1);/', $content, $match)) {
            $options['header_title_gradient_enabled'] = $match[1] === '1';
        }
        if (preg_match('/--page_background_gradient_enabled:\s*(0|1);/', $content, $match)) {
            $options['page_background_gradient_enabled'] = $match[1] === '1';
        }

        return $options;
    }

    public function colorsFromFile(string $path): array
    {
        $colors = $this->catalog->defaultColors();
        if (!file_exists($path)) {
            return $colors;
        }

        $content = file_get_contents($path);
        preg_match_all('/--([a-z_]+):\s*(#[0-9a-fA-F]{6});/', $content, $matches);
        foreach ($matches[1] ?? [] as $index => $name) {
            if (isset($colors[$name])) {
                $colors[$name] = $matches[2][$index];
            }
        }

        return $colors;
    }

    public function optionsFromFile(string $path): array
    {
        return file_exists($path)
            ? $this->optionsFromContent((string)file_get_contents($path))
            : $this->catalog->defaultOptions();
    }
}
