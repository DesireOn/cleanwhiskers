<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LayoutElementsTest extends WebTestCase
{
    public function testHeaderMainFooterPresent(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('header'));
        self::assertCount(1, $crawler->filter('main'));
        self::assertCount(1, $crawler->filter('footer'));
    }
}
