<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testHomePageDisplaysSearchForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('form'));
        self::assertCount(1, $crawler->filter('select[name="city"]'));
        self::assertCount(1, $crawler->filter('select[name="service"]'));
    }
}
