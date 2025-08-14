<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
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
}
