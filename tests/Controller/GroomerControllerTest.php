<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GroomerControllerTest extends WebTestCase
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

    public function testListSortingByRatingDesc(): void
    {
        $city = new City('Ruse');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Wash');
        $service->refreshSlugFrom($service->getName());

        $u = (new User())->setEmail('g@example.com')->setRoles([User::ROLE_GROOMER])->setPassword('hash');
        $p1 = new GroomerProfile($u, $city, 'Top', 'About');
        $p1->refreshSlugFrom($p1->getBusinessName());
        $p1->addService($service);

        $p2 = new GroomerProfile($u, $city, 'Mid', 'About');
        $p2->refreshSlugFrom($p2->getBusinessName());
        $p2->addService($service);

        $author = (new User())->setEmail('a@example.com')->setPassword('hash');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($u);
        $this->em->persist($p1);
        $this->em->persist($p2);
        $this->em->persist($author);
        $this->em->flush();

        $this->em->persist(new Review($p1, $author, 5, 'Great'));
        $this->em->persist(new Review($p2, $author, 3, 'Ok'));
        $this->em->flush();

        $this->client->request('GET', sprintf('/groomers/%s/%s?sort=rating_desc', $city->getSlug(), $service->getSlug()));

        self::assertResponseIsSuccessful();
        $crawler = $this->client->getCrawler();
        $links = $crawler->filter('#groomer-listings ul li a');
        self::assertGreaterThanOrEqual(2, $links->count());
        self::assertSame('Top', $links->eq(0)->text());
        self::assertSame('Mid', $links->eq(1)->text());
    }
}
