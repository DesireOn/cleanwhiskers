<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CitySeoIntroRenderTest extends WebTestCase
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

    public function testSeoIntroRendersWhenSet(): void
    {
        $city = new City('Testopolis');
        $city->refreshSlugFrom('Testopolis');
        $city->setSeoIntro('Amazing place for pets.');
        $this->em->persist($city);
        $this->em->flush();

        $this->client->request('GET', '/cities/'.$city->getSlug());
        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Amazing place for pets.', $content);
        $this->assertSame(3, substr_count($content, '<p>'));
    }

    public function testNoSeoIntroRendersNothing(): void
    {
        $city = new City('Plainville');
        $city->refreshSlugFrom('Plainville');
        $this->em->persist($city);
        $this->em->flush();

        $this->client->request('GET', '/cities/'.$city->getSlug());
        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertSame(2, substr_count($content, '<p>'));
    }
}
