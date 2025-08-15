<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FooterLinksRenderTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testFooterRendersLinks(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('footer .footer-nav a[href="#about"]');
        self::assertSelectorExists('footer .footer-nav a[href="#contact"]');
        self::assertSelectorExists('footer .footer-nav a[href="#faq"]');
        self::assertSelectorExists('footer .footer-legal a[href="#terms"]');
        self::assertSelectorExists('footer .footer-legal a[href="#privacy"]');
        self::assertSelectorExists('footer .back-to-top[href="#top"]');
    }
}
