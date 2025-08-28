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
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GroomerControllerTest extends WebTestCase
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

    public function testProfileDisplaysReviews(): void
    {
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $groomer = new GroomerProfile($groomerUser, $city, 'Best Groomers', 'About');
        $groomer->refreshSlugFrom($groomer->getBusinessName());

        $author = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash');
        $review = new Review($groomer, $author, 5, 'Excellent service!');
        $review->markVerified();

        $this->em->persist($groomerUser);
        $this->em->persist($city);
        $this->em->persist($groomer);
        $this->em->persist($author);
        $this->em->persist($review);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$groomer->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Excellent service!', $content);
        self::assertStringContainsString('User '.$author->getId(), $content);
        self::assertStringContainsString('★', $content);
        self::assertStringContainsString('Verified', $content);
    }

    public function testListByCityServiceFiltersByRating(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $highUser = (new User())
            ->setEmail('high@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $high = new GroomerProfile($highUser, $city, 'High', 'About');
        $high->refreshSlugFrom($high->getBusinessName());
        $high->addService($service);

        $lowUser = (new User())
            ->setEmail('low@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $low = new GroomerProfile($lowUser, $city, 'Low', 'About');
        $low->refreshSlugFrom($low->getBusinessName());
        $low->addService($service);

        $author = (new User())
            ->setEmail('author@example.com')
            ->setPassword('hash');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($highUser);
        $this->em->persist($high);
        $this->em->persist($lowUser);
        $this->em->persist($low);
        $this->em->persist($author);
        $this->em->flush();

        $this->em->persist(new Review($high, $author, 5, 'Great'));
        $this->em->persist(new Review($low, $author, 3, 'Ok'));
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug().'?rating=4');
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('High', $content);
        self::assertStringNotContainsString('Low', $content);
    }
}
