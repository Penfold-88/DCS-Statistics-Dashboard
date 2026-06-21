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
        if ($page !== null) {
            $content = (string)($page['content'] ?? '');
            $contentHtml = ($page['content_format'] ?? 'plain_text') === 'rich_html'
                ? $this->sanitizer->sanitize($content)
                : nl2br(\e($content));
        }
        return [
            'page' => $page,
            'contentHtml' => $contentHtml,
            'pageSeo' => $page ? [
                'title' => trim((string)($page['seo_title'] ?? '')) ?: (string)($page['title'] ?? ''),
                'description' => trim((string)($page['seo_description'] ?? '')),
                'image' => trim((string)($page['seo_image'] ?? '')),
            ] : [],
        ];
    }
}
