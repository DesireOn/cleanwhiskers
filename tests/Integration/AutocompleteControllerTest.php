<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\City;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AutocompleteControllerTest extends WebTestCase
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

    public function testCitiesEndpointReturnsMatchesAndLimitsResults(): void
    {
        $ruse = new City('Ruse');
        $ruse->refreshSlugFrom('Ruse');
        $this->em->persist($ruse);
        for ($i = 1; $i <= 9; ++$i) {
            $city = new City('Test '.$i);
            $city->refreshSlugFrom('Test '.$i);
            $this->em->persist($city);
        }
        $this->em->flush();

        $this->client->request('GET', '/api/autocomplete/cities', ['q' => 'Ru']);
        self::assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([['name' => 'Ruse', 'slug' => 'ruse']], $data);

        $this->client->request('GET', '/api/autocomplete/cities', ['q' => 'Test']);
        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(8, $data);
    }

    public function testServicesEndpointReturnsMatchesAndLimitsResults(): void
    {
        $grooming = (new Service())->setName('Grooming');
        $grooming->refreshSlugFrom('Grooming');
        $this->em->persist($grooming);
        for ($i = 1; $i <= 9; ++$i) {
            $service = (new Service())->setName('Test '.$i);
            $service->refreshSlugFrom('Test '.$i);
            $this->em->persist($service);
        }
        $this->em->flush();

        $this->client->request('GET', '/api/autocomplete/services', ['q' => 'Gro']);
        self::assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([['name' => 'Grooming', 'slug' => 'grooming']], $data);

        $this->client->request('GET', '/api/autocomplete/services', ['q' => 'Test']);
        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(8, $data);
    }
}
