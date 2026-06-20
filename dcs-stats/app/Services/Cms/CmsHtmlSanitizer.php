<?php

namespace DcsStats\Services\Cms;

final class CmsHtmlSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote', 'a'];
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
}
