<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u',
        'ul', 'ol', 'li', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote',
        'img', 'figure', 'figcaption', 'hr', 'pre', 'code', 'table', 'thead',
        'tbody', 'tr', 'th', 'td',
    ];

    /**
     * @var array<int, string>
     */
    private const REMOVE_ENTIRELY = [
        'script', 'style', 'iframe', 'object', 'embed',
        'form', 'input', 'button', 'select', 'textarea',
        'link', 'meta', 'base', 'title', 'noscript',
    ];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $wrapper = $document->getElementsByTagName('div')->item(0);

        if ($wrapper === null) {
            return '';
        }

        self::sanitizeNode($document, $wrapper);

        $clean = '';

        foreach ($wrapper->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return $clean;
    }

    /**
     * Teks biasa (dari textarea) menjadi HTML tersanitasi: blok yang
     * dipisah baris kosong menjadi <p>, baris tunggal menjadi <br>.
     * Semua markup diketik pengguna lolos sebagai teks (di-escape dulu).
     */
    public static function fromPlainText(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $blocks = preg_split("/\R{2,}/", $escaped) ?: [];

        $html = '';

        foreach ($blocks as $block) {
            $block = trim((string) $block);

            if ($block === '') {
                continue;
            }

            $html .= '<p>'.preg_replace("/\R/", '<br>', $block).'</p>';
        }

        return self::clean($html);
    }

    /**
     * Isi editor kaya (mis. Misi) menjadi HTML tersanitasi. HTML dari editor
     * dipakai apa adanya; teks biasa lama ("1. a\n2. b" atau "- a\n- b")
     * dikenali sebagai daftar bernomor/berpoin, selain itu jadi paragraf.
     */
    public static function fromRichOrPlain(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        if (preg_match('#<\s*(p|ol|ul|li|br|div|h[1-6]|blockquote)\b#i', $value)) {
            // Editor membungkus isi tiap poin dengan <p>; dilepas agar jarak antar poin rapat.
            $clean = preg_replace('#<li>\s*<p>(.*?)</p>\s*</li>#s', '<li>$1</li>', self::clean($value)) ?? '';

            return trim(strip_tags($clean)) === '' ? '' : $clean;
        }

        $lines = array_values(array_filter(
            array_map('trim', preg_split("/\R/", $value) ?: []),
            fn (string $line): bool => $line !== '',
        ));

        foreach ([['ol', '/^\d+[.)]\s+/'], ['ul', '/^[-*\x{2022}]\s+/u']] as [$tag, $marker]) {
            $cocok = array_filter($lines, fn (string $line): bool => (bool) preg_match($marker, $line));

            if ($lines !== [] && count($cocok) === count($lines)) {
                $items = array_map(
                    fn (string $line): string => '<li>'.htmlspecialchars(preg_replace($marker, '', $line) ?? $line, ENT_QUOTES | ENT_HTML5, 'UTF-8').'</li>',
                    $lines,
                );

                return "<{$tag}>".implode('', $items)."</{$tag}>";
            }
        }

        return self::fromPlainText($value);
    }

    /**
     * HTML tersimpan menjadi teks biasa untuk textarea: penutup blok dan
     * <br> menjadi baris baru, lalu tag dikupas dan entitas dibuka.
     */
    public static function toPlainText(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $text = (string) $html;
        $text = preg_replace('#</\s*(p|div|h[1-6]|blockquote|ul|ol|tr)\s*>#i', "\n", $text) ?? $text;
        $text = preg_replace('#<\s*br\s*/?\s*>#i', "\n", $text) ?? $text;
        $text = preg_replace('#<\s*li[^>]*>#i', '• ', $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $lines = array_map('rtrim', explode("\n", $text));
        $text = preg_replace("/\n{3,}/", "\n\n", implode("\n", $lines)) ?? '';

        return trim($text);
    }

    private static function sanitizeNode(DOMDocument $document, DOMNode $node): void
    {
        $children = [];

        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::REMOVE_ENTIRELY, true)) {
                $node->removeChild($child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                self::sanitizeNode($document, $child);
                self::unwrap($node, $child);

                continue;
            }

            self::sanitizeAttributes($child);
            self::sanitizeNode($document, $child);
        }
    }

    private static function unwrap(DOMNode $parent, DOMNode $node): void
    {
        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private static function sanitizeAttributes(DOMElement $node): void
    {
        $tag = strtolower($node->tagName);
        $toRemove = [];

        foreach ($node->attributes as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = trim($attribute->nodeValue ?? '');

            if (str_starts_with($name, 'on')) {
                $toRemove[] = $attribute->nodeName;

                continue;
            }

            if ($name === 'style') {
                $toRemove[] = $attribute->nodeName;

                continue;
            }

            if (in_array($name, ['href', 'src'], true)) {
                $allowed = ($tag === 'a' && $name === 'href' && self::isSafeUrl($value))
                    || ($tag === 'img' && $name === 'src' && self::isSafeImageSrc($value));

                if (! $allowed) {
                    $toRemove[] = $attribute->nodeName;
                }

                continue;
            }

            if (! in_array($name, ['title', 'alt', 'width', 'height', 'loading'], true)) {
                $toRemove[] = $attribute->nodeName;
            }
        }

        foreach ($toRemove as $name) {
            $node->removeAttribute($name);
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '#')) {
            return true;
        }

        if (preg_match('#^\s*(javascript|data|vbscript)\s*:#i', $url)) {
            return false;
        }

        return true;
    }

    private static function isSafeImageSrc(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        // Tolak skema aktif; izinkan http(s), path relatif (/storage/...),
        // dan data:image yang aman (hasil RichEditor Cropper).
        if (preg_match('#^\s*(javascript|vbscript)\s*:#i', $url)) {
            return false;
        }

        if (preg_match('#^\s*data\s*:#i', $url)) {
            return (bool) preg_match('#^\s*data:image/(png|jpe?g|gif|webp);base64,#i', $url);
        }

        return true;
    }
}
