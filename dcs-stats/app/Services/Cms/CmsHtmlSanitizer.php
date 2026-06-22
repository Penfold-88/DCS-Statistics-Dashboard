<?php

namespace DcsStats\Services\Cms;

final class CmsHtmlSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote', 'a', 'figure', 'figcaption', 'img', 'div'];
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'form', 'input', 'button', 'textarea', 'select', 'option'];

    public function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }
        if (!class_exists('DOMDocument')) {
            return nl2br(htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><!doctype html><html><body><div id="cms-content-root">' . $html . '</div></body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (!$loaded) {
            return '';
        }

        $root = $document->getElementById('cms-content-root');
        if (!$root) {
            return '';
        }
        foreach (iterator_to_array($root->childNodes) as $child) {
            $this->cleanNode($child);
        }

        $safe = '';
        foreach ($root->childNodes as $child) {
            $safe .= $document->saveHTML($child);
        }
        return trim($safe);
    }

    private function cleanNode(\DOMNode $node): void
    {
        if ($node->nodeType === XML_COMMENT_NODE) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
            return;
        }
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        $tag = strtolower($node->nodeName);
        if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
            return;
        }
        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->cleanNode($child);
        }
        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            $parent = $node->parentNode;
            if ($parent) {
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
            }
            return;
        }

        if ($node instanceof \DOMElement) {
            $href = $tag === 'a' ? trim($node->getAttribute('href')) : '';
            $targetBlank = $tag === 'a' && $node->getAttribute('target') === '_blank';
            $src = $tag === 'img' ? trim($node->getAttribute('src')) : '';
            $alt = $tag === 'img' ? trim($node->getAttribute('alt')) : '';
            $width = $tag === 'img' ? (int)$node->getAttribute('width') : 0;
            $height = $tag === 'img' ? (int)$node->getAttribute('height') : 0;
            $figureClass = $tag === 'figure' ? trim($node->getAttribute('class')) : '';
            $widgetClass = $tag === 'div' ? trim($node->getAttribute('class')) : '';
            $widgetServer = $tag === 'div' ? trim($node->getAttribute('data-server')) : '';
            $textAlignment = $this->textAlignment($node, $tag);
            foreach (iterator_to_array($node->attributes) as $attribute) {
                $node->removeAttribute($attribute->name);
            }
            if ($tag === 'a' && $this->safeHref($href)) {
                $node->setAttribute('href', $href);
                if ($targetBlank) {
                    $node->setAttribute('target', '_blank');
                    $node->setAttribute('rel', 'noopener noreferrer');
                }
            }
            if ($tag === 'img') {
                if (!$this->safeImageSource($src)) {
                    if ($node->parentNode) {
                        $node->parentNode->removeChild($node);
                    }
                    return;
                }
                $node->setAttribute('src', $src);
                $node->setAttribute('alt', substr($alt, 0, 300));
                if ($width > 0 && $width <= 6000) {
                    $node->setAttribute('width', (string)$width);
                }
                if ($height > 0 && $height <= 6000) {
                    $node->setAttribute('height', (string)$height);
                }
                $node->setAttribute('loading', 'lazy');
                $node->setAttribute('decoding', 'async');
            }
            if ($tag === 'figure' && in_array($figureClass, ['cms-image-left', 'cms-image-center', 'cms-image-right', 'cms-image-wide'], true)) {
                $node->setAttribute('class', $figureClass);
            }
            if ($tag === 'div') {
                if ($widgetClass !== 'cms-widget-server-status') {
                    $parent = $node->parentNode;
                    if ($parent) {
                        while ($node->firstChild) {
                            $parent->insertBefore($node->firstChild, $node);
                        }
                        $parent->removeChild($node);
                    }
                    return;
                }
                while ($node->firstChild) {
                    $node->removeChild($node->firstChild);
                }
                $node->setAttribute('class', 'cms-widget-server-status');
                $widgetServer = (string)preg_replace('/[\x00-\x1F\x7F]/u', '', $widgetServer);
                if ($widgetServer !== '') {
                    $node->setAttribute('data-server', substr($widgetServer, 0, 120));
                }
            }
            if ($textAlignment !== '') {
                $node->setAttribute('class', 'cms-text-' . $textAlignment);
            }
        }
    }

    private function safeHref(string $href): bool
    {
        if ($href === '') {
            return false;
        }
        if ($href[0] === '/' || $href[0] === '#') {
            return substr($href, 0, 2) !== '//';
        }
        $scheme = strtolower((string)parse_url($href, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }

    private function safeImageSource(string $src): bool
    {
        return preg_match('#^/?uploads/pages/[a-f0-9]{32}\.(jpg|png|webp)$#', $src) === 1;
    }

    private function textAlignment(\DOMElement $node, string $tag): string
    {
        if (!in_array($tag, ['p', 'h2', 'h3', 'h4', 'blockquote', 'li'], true)) {
            return '';
        }
        $class = trim($node->getAttribute('class'));
        if (preg_match('/(?:^|\s)cms-text-(left|center|right)(?:\s|$)/', $class, $match)) {
            return $match[1];
        }
        $align = strtolower(trim($node->getAttribute('align')));
        if (in_array($align, ['left', 'center', 'right'], true)) {
            return $align;
        }
        $style = strtolower($node->getAttribute('style'));
        if (preg_match('/(?:^|;)\s*text-align\s*:\s*(left|center|right)\s*(?:;|$)/', $style, $match)) {
            return $match[1];
        }
        return '';
    }
}
