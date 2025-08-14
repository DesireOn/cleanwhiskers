<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HeadingStructureTest extends WebTestCase
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

    public function testHeadingOutlineIsLogical(): void
    {
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $main = $crawler->filter('main');
        self::assertSame(1, $main->filter('h1')->count());

        $headings = $main->filter('h1, h2, h3, h4, h5, h6');
        $prevLevel = 1;
        foreach ($headings as $index => $node) {
            $level = (int) substr($node->nodeName, 1);
            if (0 === $index) {
                self::assertSame(1, $level);
            } else {
                self::assertLessThanOrEqual($prevLevel + 1, $level);
            }
            $prevLevel = $level;
        }
    }
}
