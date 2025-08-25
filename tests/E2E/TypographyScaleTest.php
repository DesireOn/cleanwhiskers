<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCase;

if (!class_exists(PantherTestCase::class)) {
    class TypographyScaleTest extends TestCase
    {
        public function testPantherMissing(): void
        {
            $this->markTestSkipped('Panther not installed');
        }
    }

    return;
}

use Facebook\WebDriver\WebDriverDimension;

final class TypographyScaleTest extends PantherTestCase
{
    public function testFontSizesResponsive(): void
    {
        $client = self::createPantherClient();
        $client->manage()->window()->setSize(new WebDriverDimension(375, 800));
        $client->request('GET', '/');

        $h1 = $client->executeScript('return parseFloat(getComputedStyle(document.querySelector("h1")).fontSize);');
        $h2 = $client->executeScript('return parseFloat(getComputedStyle(document.querySelector("h2")).fontSize);');
        $body = $client->executeScript('return parseFloat(getComputedStyle(document.body).fontSize);');

        self::assertGreaterThanOrEqual(27, $h1);
        self::assertLessThanOrEqual(29, $h1);
        self::assertGreaterThanOrEqual(19, $h2);
        self::assertLessThanOrEqual(21, $h2);
        self::assertGreaterThanOrEqual(15, $body);
        self::assertLessThanOrEqual(17, $body);

        $client->manage()->window()->setSize(new WebDriverDimension(1024, 800));
        $client->reload();

        $h1Desktop = $client->executeScript('return parseFloat(getComputedStyle(document.querySelector("h1")).fontSize);');
        $h2Desktop = $client->executeScript('return parseFloat(getComputedStyle(document.querySelector("h2")).fontSize);');
        $bodyDesktop = $client->executeScript('return parseFloat(getComputedStyle(document.body).fontSize);');

        self::assertGreaterThanOrEqual(28, $h1Desktop);
        self::assertLessThanOrEqual(36, $h1Desktop);
        self::assertGreaterThanOrEqual(20, $h2Desktop);
        self::assertLessThanOrEqual(24, $h2Desktop);
        self::assertGreaterThanOrEqual(16, $bodyDesktop);
        self::assertLessThanOrEqual(18, $bodyDesktop);
    }
}
