<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RobotsControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testStagingDomainDisallowsAll(): void
    {
        $this->client->request('GET', '/robots.txt', server: ['HTTP_HOST' => 'staging.cleanwhiskers.com']);
        self::assertResponseIsSuccessful();

        $content = (string) $this->client->getResponse()->getContent();
        self::assertSame("User-agent: *\nDisallow: /\n", $content);
    }

    public function testOtherDomainsServeRobotsWithSitemap(): void
    {
        $host = 'cleanwhiskers.com';
        $this->client->request('GET', '/robots.txt', server: ['HTTP_HOST' => $host]);
        self::assertResponseIsSuccessful();

        $content = (string) $this->client->getResponse()->getContent();
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        $projectDir = \is_string($projectDir) ? $projectDir : __DIR__.'/../..';
        $expected = @file_get_contents($projectDir.'/public/robots.txt');
        $expected = \is_string($expected) ? $expected : '';
        $expected .= "\nSitemap: http://{$host}/sitemap.xml";

        self::assertSame($expected, $content);
    }

    public function testWwwDomainServesRobotsWithSitemap(): void
    {
        $host = 'www.cleanwhiskers.com';
        $this->client->request('GET', '/robots.txt', server: ['HTTP_HOST' => $host]);
        self::assertResponseIsSuccessful();

        $content = (string) $this->client->getResponse()->getContent();
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        $projectDir = \is_string($projectDir) ? $projectDir : __DIR__.'/../..';
        $expected = @file_get_contents($projectDir.'/public/robots.txt');
        $expected = \is_string($expected) ? $expected : '';
        $expected .= "\nSitemap: http://{$host}/sitemap.xml";

        self::assertSame($expected, $content);
    }
}
