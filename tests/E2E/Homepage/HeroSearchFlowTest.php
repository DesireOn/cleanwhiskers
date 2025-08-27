<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use App\Entity\City;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HeroSearchFlowTest extends WebTestCase
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

    public function testTypingCityRedirectsToListing(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Mobile Dog Grooming');
        $service->refreshSlugFrom($service->getName());
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('#search-form')->form([
            'city' => $city->getSlug(),
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/groomers/'.$city->getSlug().'/'.Service::MOBILE_DOG_GROOMING);
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
