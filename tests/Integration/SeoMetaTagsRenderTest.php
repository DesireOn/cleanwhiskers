<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SeoMetaTagsRenderTest extends WebTestCase
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

    public function testCityPageHasSeoMetadata(): void
    {
        $city = new City('Testopolis');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);
        $this->em->flush();

        $this->client->request('GET', '/cities/'.$city->getSlug());
        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();

        $expectedTitle = sprintf('<title>Mobile Dog Groomers in %s – CleanWhiskers</title>', $city->getName());
        $expectedDescription = sprintf(
            '<meta name="description" content="%s">',
            htmlspecialchars(
                sprintf('Explore top mobile dog groomers in %s. Book trusted pros on CleanWhiskers.', $city->getName()),
                ENT_QUOTES
            )
        );

        $this->assertStringContainsString($expectedTitle, $content);
        $this->assertStringContainsString($expectedDescription, $content);
    }

    public function testGroomerListingHasSeoMetadata(): void
    {
        $city = new City('Gotham');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug());
        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();

        $expectedTitle = sprintf('<title>Mobile Dog Groomers in %s – CleanWhiskers</title>', $city->getName());
        $expectedDescription = sprintf(
            '<meta name="description" content="%s">',
            htmlspecialchars(
                sprintf('Browse mobile dog groomers serving %s. Read reviews and schedule your pet\'s groom today.', $city->getName()),
                ENT_QUOTES
            )
        );

        $this->assertStringContainsString($expectedTitle, $content);
        $this->assertStringContainsString($expectedDescription, $content);
    }
}
