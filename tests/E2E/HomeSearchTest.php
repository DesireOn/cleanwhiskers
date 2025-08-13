<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Entity\City;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeSearchTest extends WebTestCase
{
    public function testSearchRedirectsToListing(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());

        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Grooming');
        $service->refreshSlugFrom($service->getName());
        $em->persist($city);
        $em->persist($service);
        $em->flush();

        $client->request('GET', '/');
        $client->followRedirects(false);
        $client->submitForm('Search', [
            'city' => $city->getSlug(),
            'service' => $service->getSlug(),
        ]);
        self::assertResponseRedirects('/groomers/'.$city->getSlug().'/'.$service->getSlug());
    }
}
