<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class GroomerControllerListTest extends WebTestCase
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

    public function testListByCityAndServiceDisplaysGroomer(): void
    {
        $user = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());
        $profile = new GroomerProfile($user, $city, 'Best Groomers', 'About us');
        $profile->addService($service);
        $profile->refreshSlugFrom($profile->getBusinessName());

        $this->em->persist($user);
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($profile);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Best Groomers', (string) $this->client->getResponse()->getContent());
    }

    public function testUnknownCityReturns404(): void
    {
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());
        $this->em->persist($service);
        $this->em->flush();

        $this->client->request('GET', '/groomers/unknown-city/'.$service->getSlug());
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUnknownServiceReturns404(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/unknown-service');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
