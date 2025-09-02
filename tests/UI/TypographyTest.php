<?php

declare(strict_types=1);

namespace App\Tests\UI;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCase;

if (!class_exists(PantherTestCase::class)) {
    final class TypographyTest extends TestCase
    {
        public function testPantherMissing(): void
        {
            $this->markTestSkipped('Panther not installed');
        }
    }

    return;
}

final class TypographyTest extends PantherTestCase
{
    public function testComputedFontSizes(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/');

        $body = $client->executeScript('return window.getComputedStyle(document.body).fontSize;');
        self::assertSame('16px', $body);

        $h1 = $client->executeScript('return window.getComputedStyle(document.querySelector("h1")).fontSize;');
        self::assertSame('28px', $h1);

        $h2 = $client->executeScript('return window.getComputedStyle(document.querySelector("h2")).fontSize;');
        self::assertSame('20px', $h2);
    }
}
