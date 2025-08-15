<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SeoMetaBuilderTest extends WebTestCase
{
    public function testBlogIndexCanonicalPagination(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog?page=2');

        self::assertResponseIsSuccessful();
        self::assertSame('http://localhost/blog?page=2', $crawler->filter('link[rel="canonical"]')->attr('href'));
        self::assertSame('Blog – CleanWhiskers', $crawler->filter('meta[property="og:title"]')->attr('content'));
        self::assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    public function testBlogIndexCanonicalFirstPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog?page=1');

        self::assertResponseIsSuccessful();
        self::assertSame('http://localhost/blog', $crawler->filter('link[rel="canonical"]')->attr('href'));
    }
}
