<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SeoHeadPartialTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testHomePageHasCanonicalAndOgTags(): void
    {
        $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('<link rel="canonical" href="', $content);
        self::assertMatchesRegularExpression('/<meta property="og:title" content="[^"]+"/', $content);
        self::assertMatchesRegularExpression('/<meta property="og:description" content="[^"]+"/', $content);
    }

    public function testCityPageHasCanonicalAndOgTags(): void
    {
        $city = new City('Testopolis');
        $city->refreshSlugFrom('Testopolis');
        $this->em->persist($city);
        $this->em->flush();

        $this->client->request('GET', '/cities/'.$city->getSlug());
        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('<link rel="canonical" href="', $content);
        self::assertMatchesRegularExpression('/<meta property="og:title" content="[^"]+"/', $content);
        self::assertMatchesRegularExpression('/<meta property="og:description" content="[^"]+"/', $content);
    }
}
