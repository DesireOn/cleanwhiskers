<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCase;

if (!class_exists(PantherTestCase::class)) {
    class HeaderNavigationTest extends TestCase
    {
        public function testPantherMissing(): void
        {
            $this->markTestSkipped('Panther not installed');
        }
    }

    return;
}

use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverKeys;

final class HeaderNavigationTest extends PantherTestCase
{
    public function testDesktopNavigationShowsPrimaryLinks(): void
    {
        $client = self::createPantherClient();
        $client->manage()->window()->setSize(new WebDriverDimension(1280, 800));
        $client->request('GET', '/');

        self::assertSelectorTextContains('.nav a[href="/search"]', 'Find a Groomer');
        self::assertSelectorTextContains('.nav a[href="/register?role=groomer"]', 'List Your Business');
        self::assertSelectorTextContains('.nav a[href="/blog"]', 'Blog');
        $display = $client->executeScript('return window.getComputedStyle(document.getElementById("nav-toggle")).display;');
        self::assertSame('none', $display);
    }

    public function testMobileNavigationAndEscClosesMenu(): void
    {
        $client = self::createPantherClient();
        $client->manage()->window()->setSize(new WebDriverDimension(375, 667));
        $client->request('GET', '/');

        self::assertSelectorExists('#nav-toggle');
        $display = $client->executeScript('return window.getComputedStyle(document.querySelector(".header__cta--mobile")).display;');
        self::assertNotSame('none', $display);

        self::assertSelectorExists('footer a[href="#about"]');
        self::assertSelectorNotExists('header a[href="#about"]');
        self::assertSelectorExists('footer a[href="#contact"]');
        self::assertSelectorNotExists('header a[href="#contact"]');
        self::assertSelectorExists('footer a[href="#faq"]');
        self::assertSelectorNotExists('header a[href="#faq"]');
        self::assertSelectorExists('footer a[href="#terms"]');
        self::assertSelectorNotExists('header a[href="#terms"]');
        self::assertSelectorExists('footer a[href="#privacy"]');
        self::assertSelectorNotExists('header a[href="#privacy"]');

        $client->executeScript('document.getElementById("nav-toggle").click();');
        $expanded = $client->executeScript('return document.getElementById("nav-toggle").getAttribute("aria-expanded");');
        self::assertSame('true', $expanded);

        $client->getKeyboard()->sendKeys([WebDriverKeys::ESCAPE]);
        $expanded = $client->executeScript('return document.getElementById("nav-toggle").getAttribute("aria-expanded");');
        self::assertSame('false', $expanded);
        $active = $client->executeScript('return document.activeElement.id');
        self::assertSame('nav-toggle', $active);
    }
}
