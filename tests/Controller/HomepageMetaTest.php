<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomepageMetaTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testHomePageRendersMetaTags(): void
    {
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('title', 'Book local pet care | CleanWhiskers');
        $description = $crawler->filter('meta[name="description"]')->attr('content');
        self::assertSame(
            'Discover trusted groomers and pet boarding near you. CleanWhiskers makes booking easy.',
            $description
        );
    }
}
