<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\ContentSanitizer;
use PHPUnit\Framework\TestCase;

final class ContentSanitizerTest extends TestCase
{
    public function testSanitizeRemovesScriptsAndAttributes(): void
    {
        $sanitizer = new ContentSanitizer();
        $html = '<p onclick="alert(1)">Hi<script>alert(1)</script></p>';
        $clean = $sanitizer->sanitize($html);

        self::assertStringNotContainsString('onclick', $clean);
        self::assertStringNotContainsString('<script', $clean);
        self::assertSame('<p>Hi</p>', $clean);
    }

    public function testSanitizePreservesCodeBlocks(): void
    {
        $sanitizer = new ContentSanitizer();
        $html = '<code>alert(1);</code>';
        $clean = $sanitizer->sanitize($html);

        self::assertSame('<code>alert(1);</code>', $clean);
    }

    public function testComputeReadingMinutes(): void
    {
        $sanitizer = new ContentSanitizer();
        $words = str_repeat('word ', 400); // 400 words => 2 minutes
        $html = '<p>'.$words.'</p>';
        $minutes = $sanitizer->computeReadingMinutes($html);

        self::assertSame(2, $minutes);
    }
}
