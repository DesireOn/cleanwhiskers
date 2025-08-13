<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class MenuToggleTest extends PantherTestCase
{
    public function testMenuAriaExpandedToggles(): void
    {
        $client = self::createPantherClient();
        $crawler = $client->request('GET', '/');

        $button = $crawler->filter('button.menu-toggle');
        self::assertSame('false', $button->attr('aria-expanded'));

        $button->click();
        $client->waitForVisibility('#primary-menu');
        $crawler = $client->getCrawler();
        $button = $crawler->filter('button.menu-toggle');
        self::assertSame('true', $button->attr('aria-expanded'));

        $button->click();
        $client->waitForInvisibility('#primary-menu');
        $crawler = $client->getCrawler();
        $button = $crawler->filter('button.menu-toggle');
        self::assertSame('false', $button->attr('aria-expanded'));
    }
}
