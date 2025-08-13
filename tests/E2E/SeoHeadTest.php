<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SeoHeadTest extends WebTestCase
{
    public function testCanonicalOnHomepage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('link[rel="canonical"]'));
    }

    public function testCanonicalOnCityPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/cities/sofia');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('link[rel="canonical"]'));
    }
}
