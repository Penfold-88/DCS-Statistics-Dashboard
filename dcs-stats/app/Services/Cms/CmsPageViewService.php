<?php

namespace DcsStats\Services\Cms;

final class CmsPageViewService
{
    private CmsHtmlSanitizer $sanitizer;

    public function __construct(?CmsHtmlSanitizer $sanitizer = null)
    {
        $this->sanitizer = $sanitizer ?? new CmsHtmlSanitizer();
    }

    public function state(?array $page): array
    {
        $contentHtml = '';
        $hasServerStatusWidget = false;
        $hasImageGallery = false;
        if ($page !== null) {
            $content = (string)($page['content'] ?? '');
            $contentHtml = ($page['content_format'] ?? 'plain_text') === 'rich_html'
                ? $this->sanitizer->sanitize($content)
                : nl2br(\e($content));
            $pattern = '#<div class="cms-widget-server-status"(?: data-server=(["\'])(.*?)\1)?></div>#';
            $hasServerStatusWidget = preg_match($pattern, $contentHtml) === 1;
            if ($hasServerStatusWidget) {
                $loading = \e(\dcs_t('widget.server_status.loading'));
                $contentHtml = (string)preg_replace_callback(
                    $pattern,
                    static function (array $match) use ($loading): string {
                        $server = html_entity_decode((string)($match[2] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        return '<section class="dcs-server-status-widget" data-server-status-widget data-server-filter="' . \e($server) . '" aria-live="polite"><p class="dcs-widget-loading">' . $loading . '</p></section>';
                    },
                    $contentHtml
                );
            }
            $galleryPattern = '#<div class="cms-widget-image-gallery" data-gallery="([a-f0-9]{16})"></div>#';
            $hasImageGallery = \isFeatureEnabled('cms_gallery_enabled') && preg_match($galleryPattern, $contentHtml) === 1;
            if ($hasImageGallery) {
                $contentHtml = (string)preg_replace_callback($galleryPattern, function (array $match): string {
                    return $this->renderGallery((string)$match[1]);
                }, $contentHtml);
            } else {
                $contentHtml = (string)preg_replace($galleryPattern, '', $contentHtml);
            }
        }
        return [
            'page' => $page,
            'contentHtml' => $contentHtml,
            'hasServerStatusWidget' => $hasServerStatusWidget,
            'hasImageGallery' => $hasImageGallery,
            'pageSeo' => $page ? [
                'title' => trim((string)($page['seo_title'] ?? '')) ?: (string)($page['title'] ?? ''),
                'description' => trim((string)($page['seo_description'] ?? '')),
                'image' => trim((string)($page['seo_image'] ?? '')),
            ] : [],
        ];
    }

    private function renderGallery(string $id): string
    {
        $gallery = (new CmsGalleryStore())->find($id);
        if (!$gallery || empty($gallery['enabled'])) return '';
        $media = [];
        foreach ((new CmsMediaService())->items() as $item) $media[(string)$item['id']] = $item;
        $galleryItems = [];
        foreach ((array)($gallery['items'] ?? []) as $selected) {
            $item = $media[(string)($selected['media_id'] ?? '')] ?? null;
            if (!$item) continue;
            $src = (string)$item['path'];
            $alt = trim((string)($selected['alt'] ?? '')) ?: (string)($item['original_name'] ?? '');
            $caption = trim((string)($selected['caption'] ?? ''));
            $galleryItems[] = ['src' => $src, 'alt' => $alt, 'caption' => $caption, 'width' => (int)($item['width'] ?? 0), 'height' => (int)($item['height'] ?? 0)];
        }
        if (!$galleryItems) return '';
        $first = $galleryItems[0];
        $html = '<section class="cms-image-gallery" data-gallery-carousel aria-label="' . \e((string)($gallery['title'] ?? '')) . '"><figure class="cms-gallery-stage"><button type="button" data-gallery-featured data-full-src="' . \e($first['src']) . '" data-caption="' . \e($first['caption']) . '"><img src="' . \e($first['src']) . '" alt="' . \e($first['alt']) . '" width="' . $first['width'] . '" height="' . $first['height'] . '"></button><figcaption data-gallery-caption>' . \e($first['caption']) . '</figcaption></figure><div class="cms-gallery-thumbnails" role="list">';
        foreach ($galleryItems as $index => $galleryItem) {
            $html .= '<button type="button" role="listitem" data-gallery-thumb data-full-src="' . \e($galleryItem['src']) . '" data-caption="' . \e($galleryItem['caption']) . '" data-alt="' . \e($galleryItem['alt']) . '" class="' . ($index === 0 ? 'is-active' : '') . '" aria-current="' . ($index === 0 ? 'true' : 'false') . '"><img src="' . \e($galleryItem['src']) . '" alt="" loading="lazy" decoding="async"></button>';
        }
        return $html . '</div></section>';
    }
}
