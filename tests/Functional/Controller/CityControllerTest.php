<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CityControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testShowReturns200WhenCityExists(): void
    {
        $city = new City('Sofia');
        $this->em->persist($city);
        $this->em->flush();

        $this->client->request('GET', '/cities/sofia');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('title', 'Sofia');
    }

    public function testShowReturns404WhenCityDoesNotExist(): void
    {
        $this->client->request('GET', '/cities/unknown');

        self::assertResponseStatusCodeSame(404);
    }

    public function testShowRedirectsTrailingSlash(): void
    {
        $city = new City('Sofia');
        $this->em->persist($city);
        $this->em->flush();

        $this->client->request('GET', '/cities/sofia/');

        self::assertResponseRedirects('/cities/sofia', Response::HTTP_MOVED_PERMANENTLY);
    }
}
