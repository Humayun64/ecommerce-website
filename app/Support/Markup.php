<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * A small formatter for page and post content.
 *
 * Everything is escaped first and then a handful of marks are turned into
 * markup, so content gets headings, lists, images, quotes and links without
 * ever being able to carry a script tag.
 */
class Markup
{
    public static function render(?string $text): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        $text = e($text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $html  = [];
        $list  = [];
        $quote = [];

        $flushList = function () use (&$list, &$html) {
            if ($list) {
                $html[] = '<ul>' . implode('', array_map(fn ($i) => '<li>' . $i . '</li>', $list)) . '</ul>';
                $list = [];
            }
        };

        $flushQuote = function () use (&$quote, &$html) {
            if ($quote) {
                $html[] = '<blockquote>' . implode(' ', array_map(fn ($q) => '<p>' . $q . '</p>', $quote)) . '</blockquote>';
                $quote = [];
            }
        };

        foreach (explode("\n", $text) as $line) {
            $line = trim($line);

            if ($line === '') {
                $flushList();
                $flushQuote();
                continue;
            }

            if (Str::startsWith($line, '### ')) {
                $flushList(); $flushQuote();
                $inner = self::inline(substr($line, 4));
                $html[] = '<h4>' . $inner . '</h4>';
            } elseif (Str::startsWith($line, '## ')) {
                $flushList(); $flushQuote();
                $raw = trim(substr($line, 3));
                // Anchored so a contents list can link to it.
                $html[] = '<h3 id="' . Str::slug(html_entity_decode($raw)) . '">' . self::inline($raw) . '</h3>';
            } elseif (Str::startsWith($line, '&gt; ')) {
                // The marker is checked after escaping, so it is &gt; by now.
                $flushList();
                $quote[] = self::inline(substr($line, 5));
            } elseif (Str::startsWith($line, '- ')) {
                $flushQuote();
                $list[] = self::inline(substr($line, 2));
            } elseif (preg_match('/^!\[([^\]]*)\]\(([^)\s]+)\)$/', $line, $m)) {
                $flushList(); $flushQuote();
                $src = self::safeUrl($m[2]);

                if ($src) {
                    $html[] = '<figure><img src="' . $src . '" alt="' . $m[1] . '" loading="lazy">'
                        . ($m[1] !== '' ? '<figcaption>' . $m[1] . '</figcaption>' : '')
                        . '</figure>';
                }
            } else {
                $flushList(); $flushQuote();
                $html[] = '<p>' . self::inline($line) . '</p>';
            }
        }

        $flushList();
        $flushQuote();

        return implode("\n", $html);
    }

    /** **bold**, *italic* and [text](url) — nothing else. */
    private static function inline(string $line): string
    {
        $line = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $line);
        $line = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $line);

        return preg_replace_callback(
            '/\[([^\]]+)\]\(([^)\s]+)\)/',
            function ($m) {
                $url = self::safeUrl($m[2]);

                if (! $url) {
                    return $m[1];
                }

                $external = Str::startsWith($url, ['http://', 'https://']);

                return '<a href="' . $url . '"'
                    . ($external ? ' target="_blank" rel="noopener"' : '')
                    . '>' . $m[1] . '</a>';
            },
            $line
        );
    }

    /** Only ordinary links survive; javascript: and friends are dropped. */
    private static function safeUrl(string $url): ?string
    {
        $url = trim($url);

        return preg_match('#^(https?://|/|\#|mailto:|tel:)#i', $url) ? $url : null;
    }
}
