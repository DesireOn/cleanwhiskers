<?php

declare(strict_types=1);

namespace App\Service;

final class ContentSanitizer
{
    /** @var object|null */
    private $sanitizer;

    /**
     * @param array<int,string> $allowedTags
     */
    public function __construct(private array $allowedTags = ['p', 'a', 'code', 'pre', 'em', 'strong', 'ul', 'ol', 'li', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'])
    {
        if (class_exists(\Symfony\Component\HtmlSanitizer\HtmlSanitizer::class)) {
            $class = \Symfony\Component\HtmlSanitizer\HtmlSanitizer::class;
            $this->sanitizer = new $class([
                'allow_safe_elements' => true,
                'allowed_elements' => $this->allowedTags,
                'allowed_attributes' => ['href', 'title', 'src', 'alt'],
            ]);
        } else {
            $this->sanitizer = null;
        }
    }

    public function sanitize(string $html): string
    {
        if (is_object($this->sanitizer) && method_exists($this->sanitizer, 'sanitize')) {
            return $this->sanitizer->sanitize($html); // @phpstan-ignore-line
        }

        $allowed = '<'.implode('><', $this->allowedTags).'>';
        $clean = preg_replace('#<script[^>]*>.*?</script>#is', '', $html) ?? '';
        $clean = strip_tags($clean, $allowed);
        $clean = preg_replace('/\son\w+="[^"]*"/i', '', $clean) ?? '';
        $clean = preg_replace("/\son\w+='[^']*'/i", '', $clean) ?? '';

        return $clean;
    }

    public function computeReadingMinutes(string $html): int
    {
        $text = trim(strip_tags($html));
        $words = str_word_count($text);

        return max(1, (int) ceil($words / 200));
    }
}
