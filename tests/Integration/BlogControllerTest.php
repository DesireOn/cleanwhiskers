<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BlogControllerTest extends WebTestCase
{
    public function testBlogIndexListsPosts(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog');

        self::assertResponseIsSuccessful();
        self::assertSame(2, $crawler->filter('li.post')->count());
        self::assertSelectorTextContains('h1', 'Blog');
    }

    public function testBlogShowDisplaysPostWithSeo(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/welcome-to-cleanwhiskers');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Welcome to CleanWhiskers');
        $content = $client->getResponse()->getContent();
        self::assertStringContainsString('<title>Welcome to CleanWhiskers Blog – CleanWhiskers</title>', $content);
        self::assertStringContainsString('<meta name="description" content="Learn about our mission and how we help pet owners.">', $content);
    }
}
