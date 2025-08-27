<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RobotsTest extends WebTestCase
{
    public function testRobotsTxtServed(): void
    {
        $client = static::createClient();
        $client->request('GET', '/robots.txt');
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Disallow: /admin', $content);
        self::assertStringContainsString('Disallow: /blog/tag/*?page=', $content);
    }
}
