<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FeaturedGroomersCarouselTest extends WebTestCase
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

    public function testCarouselMarkupAppearsWhenMoreThanFourGroomers(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Grooming');
        $service->refreshSlugFrom($service->getName());

        $this->em->persist($city);
        $this->em->persist($service);

        for ($i = 0; $i < 5; ++$i) {
            $gUser = (new User())
                ->setEmail(sprintf('g%d@example.com', $i))
                ->setPassword('hash')
                ->setRoles([User::ROLE_GROOMER]);
            $author = (new User())
                ->setEmail(sprintf('a%d@example.com', $i))
                ->setPassword('hash');
            $profile = new GroomerProfile($gUser, $city, 'Groomer '.$i, 'About');
            $profile->setPriceRange('$10');
            $profile->addService($service);
            $this->em->persist($gUser);
            $this->em->persist($author);
            $this->em->persist($profile);
            $this->em->persist(new Review($profile, $author, 5, 'Great'));
        }
        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#featured-groomers [data-carousel]');
        self::assertCount(5, $crawler->filter('#featured-groomers .carousel__card'));
        self::assertSelectorExists('#featured-groomers .card-groomer__price');
        self::assertSelectorTextContains('#featured-groomers .card-groomer__book', 'Book Now');
    }
}
