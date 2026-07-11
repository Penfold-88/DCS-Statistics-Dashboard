<?php

namespace DcsStats\Services\Admin;

final class ThemeColorService
{
    private ThemeColorCatalog $catalog;
    private ThemeColorCssParser $parser;
    private ThemeColorSanitizer $sanitizer;
    private ThemeCustomCssBuilder $cssBuilder;

    public function __construct(
        ?ThemeColorCatalog $catalog = null,
        ?ThemeColorCssParser $parser = null,
        ?ThemeColorSanitizer $sanitizer = null,
        ?ThemeCustomCssBuilder $cssBuilder = null
    ) {
        $this->catalog = $catalog ?? new ThemeColorCatalog();
        $this->parser = $parser ?? new ThemeColorCssParser($this->catalog);
        $this->sanitizer = $sanitizer ?? new ThemeColorSanitizer($this->catalog);
        $this->cssBuilder = $cssBuilder ?? new ThemeCustomCssBuilder($this->catalog);
    }

    public function defaultColors(): array
    {
        return $this->catalog->defaultColors();
    }

    public function colorGroups(): array
    {
        return $this->catalog->colorGroups();
    }

    public function defaultOptions(): array
    {
        return $this->catalog->defaultOptions();
    }

    public function loadOptionsFromCss(string $content): array
    {
        return $this->parser->optionsFromContent($content);
    }

    public function loadColorsFromCss(string $customCssPath): array
    {
        return $this->parser->colorsFromFile($customCssPath);
    }

    public function loadOptionsFile(string $customCssPath): array
    {
        return $this->parser->optionsFromFile($customCssPath);
    }

    public function cleanColors($colors): array
    {
        return $this->sanitizer->colors($colors);
    }

    public function buildCustomCss(array $colors, array $options = []): string
    {
        return $this->cssBuilder->build($colors, $options);
    }

    public function cleanOptions($options): array
    {
        return $this->sanitizer->options($options);
    }
}
