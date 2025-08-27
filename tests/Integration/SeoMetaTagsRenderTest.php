<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\City;
use App\Entity\Service;
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

    public function testGroomerListingHasSeoMetadata(): void
    {
        $city = new City('Gotham');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Mobile Dog Grooming');
        $service->refreshSlugFrom($service->getName());
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug());
        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();

        $expectedTitle = sprintf(
            '<title>Mobile Dog Groomers in %s for %s – CleanWhiskers</title>',
            $city->getName(),
            $service->getName()
        );
        $expectedDescription = sprintf(
            '<meta name="description" content="%s">',
            htmlspecialchars(
                sprintf(
                    'Find mobile dog groomers in %s for %s. Book experienced groomers on CleanWhiskers.',
                    $city->getName(),
                    $service->getName()
                ),
                ENT_QUOTES
            )
        );

        $this->assertStringContainsString($expectedTitle, $content);
        $this->assertStringContainsString($expectedDescription, $content);
    }
}
