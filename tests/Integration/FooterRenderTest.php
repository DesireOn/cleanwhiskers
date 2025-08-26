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
        self::assertSelectorExists('footer .footer-nav nav[aria-label="Site"] a[href="#about"]');
        self::assertSelectorExists('footer .footer-nav nav[aria-label="Site"] a[href="#contact"]');
        self::assertSelectorExists('footer .footer-nav nav[aria-label="Site"] a[href="#faq"]');
        self::assertSelectorExists('footer .footer-nav nav[aria-label="Legal"] a[href="#terms"]');
        self::assertSelectorExists('footer .footer-nav nav[aria-label="Legal"] a[href="#privacy"]');
        self::assertSelectorExists('footer .footer-social a[href="https://twitter.com/cleanwhiskers"][rel="noopener"]');
        self::assertSelectorExists('footer .footer-social a[href="https://facebook.com/cleanwhiskers"][rel="noopener"]');
        self::assertSelectorExists('footer .footer-social a[href="https://instagram.com/cleanwhiskers"][rel="noopener"]');
        self::assertSelectorExists('footer form.footer-search input[name="city"]');
    }
}
