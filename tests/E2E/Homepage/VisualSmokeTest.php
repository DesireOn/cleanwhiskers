<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use PHPUnit\Framework\TestCase;

final class VisualSmokeTest extends TestCase
{
    public function testCssVariablesAndContrast(): void
    {
        $css = file_get_contents(__DIR__.'/../../../assets/styles/app.css');
        self::assertNotFalse($css);

        self::assertStringContainsString('--color-accent', $css);
        self::assertStringContainsString('--space-1', $css);

        $textColor = $this->extractColor($css, '--color-text');
        $backgroundColor = $this->extractColor($css, '--color-cream');
        $accentColor = $this->extractColor($css, '--color-accent');

        $bodyContrast = $this->contrastRatio($textColor, $backgroundColor);
        $accentContrast = $this->contrastRatio($accentColor, $textColor);

        self::assertGreaterThanOrEqual(4.5, $bodyContrast);
        self::assertGreaterThanOrEqual(4.5, $accentContrast);
    }

    /**
     * @return int[]
     */
    private function extractColor(string $css, string $variable): array
    {
        $pattern = sprintf('/%s:\s*([^;]+);/', preg_quote($variable, '/'));
        if (1 !== preg_match($pattern, $css, $matches)) {
            self::fail(sprintf('Variable %s not found', $variable));
        }

        return $this->hexToRgb(trim($matches[1]));
    }

    /**
     * @param int[] $a
     * @param int[] $b
     */
    private function contrastRatio(array $a, array $b): float
    {
        $l1 = $this->luminance($a);
        $l2 = $this->luminance($b);
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * @param int[] $rgb
     */
    private function luminance(array $rgb): float
    {
        $channels = array_map(static function (int $value): float {
            $channel = $value / 255;

            return $channel <= 0.03928 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    /**
     * @return int[]
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (3 === strlen($hex)) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
