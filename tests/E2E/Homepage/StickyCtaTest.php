<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCase;

if (!class_exists(PantherTestCase::class)) {
    class StickyCtaTest extends TestCase
    {
        public function testPantherMissing(): void
        {
            $this->markTestSkipped('Panther not installed');
        }
    }

    return;
}

use Facebook\WebDriver\WebDriverDimension;

final class StickyCtaTest extends PantherTestCase
{
    public function testStickyCtaVisibleOnMobile(): void
    {
        $client = self::createPantherClient();
        $client->manage()->window()->setSize(new WebDriverDimension(375, 667));
        $client->request('GET', '/');

        self::assertSelectorExists('.sticky-cta__btn');
        $display = $client->executeScript('return window.getComputedStyle(document.querySelector(".back-to-top")).display;');
        self::assertSame('none', $display);
    }
}
