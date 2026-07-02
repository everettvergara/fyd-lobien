<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * @var array<int, string>
     */
    protected static array $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'a', 'blockquote', 'pre', 'code', 'span', 'div',
        'img', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    public static function clean(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        $allowed = '<'.implode('><', self::$allowedTags).'>';

        return strip_tags($html, $allowed);
    }
}
