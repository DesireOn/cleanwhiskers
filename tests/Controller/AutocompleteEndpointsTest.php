<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AutocompleteEndpointsTest extends WebTestCase
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

    public function testCitiesEndpointReturnsMatchesAndEnforcesRules(): void
    {
        $ruse = new City('Ruse');
        $ruse->refreshSlugFrom($ruse->getName());
        $this->em->persist($ruse);
        for ($i = 1; $i <= 9; ++$i) {
            $city = new City('City'.$i);
            $city->refreshSlugFrom($city->getName());
            $this->em->persist($city);
        }
        $this->em->flush();

        $this->client->request('GET', '/api/autocomplete/cities?q=ru');
        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('[{"name":"Ruse","slug":"ruse"}]', (string) $this->client->getResponse()->getContent());

        $this->client->request('GET', '/api/autocomplete/cities?q=r');
        self::assertSame('[]', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/api/autocomplete/cities?q=city');
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertCount(8, $data);
    }

    public function testServicesEndpointReturnsMatches(): void
    {
        $dog = (new Service())->setName('Dog Grooming');
        $dog->refreshSlugFrom($dog->getName());
        $this->em->persist($dog);
        $this->em->flush();

        $this->client->request('GET', '/api/autocomplete/services?q=dog');
        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('[{"name":"Dog Grooming","slug":"dog-grooming"}]', (string) $this->client->getResponse()->getContent());
    }
}
