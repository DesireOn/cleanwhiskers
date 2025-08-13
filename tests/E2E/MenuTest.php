<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\TestCase;

class MenuTest extends TestCase
{
    public function testMenuToggles(): void
    {
        if (!class_exists(\Symfony\Component\Panther\PantherTestCase::class)) {
            $this->markTestSkipped('Panther not installed');
            return;
        }

        $client = \Symfony\Component\Panther\PantherTestCase::createPantherClient();
        $crawler = $client->request('GET', '/');
        $button = $crawler->filter('.topbar__burger');
        self::assertSame('false', $button->attr('aria-expanded'));
        $button->click();
        self::assertSame('true', $button->attr('aria-expanded'));
    }
}
