<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCase;

if (!class_exists(PantherTestCase::class)) {
    class StickySearchTest extends TestCase
    {
        public function testPantherMissing(): void
        {
            $this->markTestSkipped('Panther not installed');
        }
    }

    return;
}

final class StickySearchTest extends PantherTestCase
{
    public function testStickySearchCompactsOnScroll(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/');

        self::assertSelectorNotExists('#sticky-search.search--compact');

        $client->executeScript('window.scrollTo(0, 250); window.dispatchEvent(new Event("scroll"));');
        $client->waitFor('#sticky-search.search--compact');

        self::assertSelectorExists('#sticky-search.search--compact');

        $cityVisible = $client->executeScript('return window.getComputedStyle(document.getElementById("sticky-city")).display !== "none";');
        self::assertTrue($cityVisible);

        $serviceHidden = $client->executeScript('return window.getComputedStyle(document.getElementById("sticky-service")).display === "none";');
        self::assertTrue($serviceHidden);

        $client->executeScript('window.scrollTo(0, 0); window.dispatchEvent(new Event("scroll"));');
        $client->waitFor('#sticky-search:not(.search--compact)');

        self::assertSelectorNotExists('#sticky-search.search--compact');
    }
}
