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
        }
        return [
            'page' => $page,
            'contentHtml' => $contentHtml,
            'hasServerStatusWidget' => $hasServerStatusWidget,
            'pageSeo' => $page ? [
                'title' => trim((string)($page['seo_title'] ?? '')) ?: (string)($page['title'] ?? ''),
                'description' => trim((string)($page['seo_description'] ?? '')),
                'image' => trim((string)($page['seo_image'] ?? '')),
            ] : [],
        ];
    }
}
