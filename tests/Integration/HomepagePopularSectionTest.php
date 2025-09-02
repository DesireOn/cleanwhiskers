<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomepagePopularSectionTest extends WebTestCase
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

    public function testPopularSectionLinksAreRendered(): void
    {
        self::markTestSkipped('Popular section removed');
    }
}
