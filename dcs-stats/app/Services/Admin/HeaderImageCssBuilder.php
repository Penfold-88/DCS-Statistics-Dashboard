<?php

namespace DcsStats\Services\Admin;

final class HeaderImageCssBuilder
{
    private ThemeAssetService $assetService;

    public function __construct(?ThemeAssetService $assetService = null)
    {
        $this->assetService = $assetService ?? new ThemeAssetService();
    }

    public function build(array $settings): string
    {
        $imageUrl = $this->assetService->cssImageUrl($settings['image']);
        $css = ".header-background {\n";
        $css .= "    background-image: url('{$imageUrl}') !important;\n";
        $css .= "    background-size: cover !important;\n";
        $css .= "    background-position: {$settings['position_x']}% {$settings['position_y']}% !important;\n";
        $css .= "    background-repeat: no-repeat !important;\n";
        $css .= "}\n";

        if (!empty($settings['background_image'])) {
            $backgroundUrl = $this->assetService->cssImageUrl($settings['background_image']);
            $css .= "\nbody {\n";
            $css .= "    background-color: var(--background_color, #121212) !important;\n";
            $css .= "    background-image: linear-gradient(rgba(0, 0, 0, 0.58), rgba(0, 0, 0, 0.58)), url('{$backgroundUrl}') !important;\n";
            $css .= "    background-attachment: fixed !important;\n";
            $css .= "    background-position: center center, {$settings['background_position_x']}% {$settings['background_position_y']}% !important;\n";
            $css .= "    background-repeat: no-repeat, no-repeat !important;\n";
            $css .= "    background-size: cover, {$settings['background_zoom']}% auto !important;\n";
            $css .= "}\n";
            $css .= "\n@media (max-width: 768px) {\n";
            $css .= "    body {\n";
            $css .= "        background: var(--page_background_css, var(--background_color, #121212)) !important;\n";
            $css .= "        background-attachment: scroll !important;\n";
            $css .= "    }\n";
            $css .= "}\n";
        }

        return $css;
    }
}
