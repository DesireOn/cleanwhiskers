<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\City;
use App\Entity\Service;
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
        foreach (['Bucharest', 'Ruse', 'Sofia'] as $name) {
            $city = new City($name);
            $city->setSeoIntro('Visit '.$name);
            $this->em->persist($city);
        }

        $service = (new Service())->setName('Grooming');
        $this->em->persist($service);

        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        foreach (['bucharest', 'ruse', 'sofia'] as $slug) {
            self::assertSelectorExists(sprintf('.popular-cities__link[href="/cities/%s"]', $slug));
        }

        $firstCitySlug = 'bucharest';
        self::assertSelectorExists(sprintf('#popular-services a[href="/groomers/%s/grooming"]', $firstCitySlug));
        self::assertSelectorNotExists('#popular-services a[href*="boarding"]');
    }
}
