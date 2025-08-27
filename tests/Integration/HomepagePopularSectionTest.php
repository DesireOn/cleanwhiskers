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
        $mobileService = (new Service())->setName('Mobile Dog Grooming');
        $mobileService->refreshSlugFrom('Mobile Dog Grooming');
        $this->em->persist($mobileService);

        $groomingService = (new Service())->setName('Grooming');
        $this->em->persist($groomingService);

        foreach (['Bucharest', 'Ruse', 'Sofia'] as $name) {
            $city = new City($name);
            $city->setSeoIntro('Visit '.$name);
            $this->em->persist($city);

            $groomer = new \App\Entity\GroomerProfile(null, $city, $name.' Groomer', 'About');
            $groomer->getServices()->add($mobileService);
            $this->em->persist($groomer);
        }

        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        foreach (['bucharest', 'ruse', 'sofia'] as $slug) {
            self::assertSelectorExists(sprintf(
                '.popular-cities__link[href="/groomers/%s/%s"]',
                $slug,
                Service::MOBILE_DOG_GROOMING
            ));
        }

        $firstCitySlug = 'bucharest';
        self::assertSelectorExists(sprintf('#popular-services a[href="/groomers/%s/grooming"]', $firstCitySlug));
        self::assertSelectorNotExists('#popular-services a[href*="boarding"]');
    }
}
