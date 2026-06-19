<?php

namespace DcsStats\Services\Admin;

final class CssContentValidator
{
    public function isSafe(string $css): bool
    {
        if (strlen($css) > 1048576 || strpos($css, "\0") !== false) {
            return false;
        }

        if (preg_match('/@import|expression\s*\(|javascript\s*:|vbscript\s*:|behavior\s*:|-moz-binding/i', $css)) {
            return false;
        }

        if (preg_match_all('/url\(\s*([\'\"]?)(.*?)\1\s*\)/is', $css, $matches)) {
            foreach ($matches[2] as $url) {
                $url = trim((string)$url);
                if ($url === '' || $url[0] === '#' || ($url[0] === '/' && strpos($url, '//') !== 0)) {
                    continue;
                }
                if (preg_match('#^data:image/(?:png|jpeg|gif|webp);base64,#i', $url)) {
                    continue;
                }
                if (strpos($url, ':') !== false || strpos($url, '//') === 0 || strpos($url, '\\') !== false) {
                    return false;
                }
            }
        }

        return true;
    }
}
