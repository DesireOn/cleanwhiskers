<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StickySearchTest extends WebTestCase
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

    public function testStickySearchFormSubmits(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        self::assertSelectorExists('#sticky-search');

        $form = $crawler->filter('#sticky-search-form')->form([
            'city' => $city->getSlug(),
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/groomers/'.$city->getSlug());
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testStickySearchHasCityAutocomplete(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        self::assertSelectorExists('#sticky-city[role="combobox"][aria-controls="city-list"]');
        self::assertSelectorExists(sprintf('#city-list [data-value="%s"]', $city->getSlug()));
    }
}
