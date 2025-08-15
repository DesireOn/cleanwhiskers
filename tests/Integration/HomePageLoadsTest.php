<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomePageLoadsTest extends WebTestCase
{
    public function testHomePageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input#city');
        self::assertSelectorExists('input#service[type="hidden"][value="grooming"]');
        self::assertSelectorTextContains('button#search-submit', 'Find a Groomer');
        self::assertSelectorTextContains('a.hero__cta-link', 'List Your Business');
    }
}
