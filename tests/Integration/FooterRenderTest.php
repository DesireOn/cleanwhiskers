<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FooterRenderTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testFooterRendersLinksAndWidgets(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('footer .footer-nav a[href="#about"]');
        self::assertSelectorExists('footer .footer-nav a[href="#contact"]');
        self::assertSelectorExists('footer .footer-nav a[href="#faq"]');
        self::assertSelectorExists('footer .footer-legal a[href="#terms"]');
        self::assertSelectorExists('footer .footer-legal a[href="#privacy"]');
        self::assertSelectorExists('footer .trust-seal--ssl');
        self::assertSelectorExists('footer .trust-seal--reviews');
        self::assertSelectorExists('footer .footer-social a[href="https://twitter.com/cleanwhiskers"][rel="noopener"]');
        self::assertSelectorExists('footer .footer-social a[href="https://facebook.com/cleanwhiskers"][rel="noopener"]');
        self::assertSelectorExists('footer .footer-social a[href="https://instagram.com/cleanwhiskers"][rel="noopener"]');
        self::assertSelectorExists('footer form.footer-search input[name="city"]');
        self::assertSelectorExists('footer .back-to-top[href="#top"]');
    }
}
