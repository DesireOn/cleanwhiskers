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
use Symfony\Component\HttpFoundation\Response;

final class ReviewControllerTest extends WebTestCase
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

    public function testAnonymousUserCannotAccessForm(): void
    {
        $groomer = $this->persistGroomer();

        $this->client->request('GET', '/groomers/'.$groomer->getSlug().'/reviews/new');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoggedInUserCanSubmitReview(): void
    {
        $groomer = $this->persistGroomer();

        $user = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash');
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/groomers/'.$groomer->getSlug().'/reviews/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Submit')->form([
            'review_form[rating]' => 5,
            'review_form[comment]' => '<b>Great service</b>',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/groomers/'.$groomer->getSlug());
        $this->client->followRedirect();

        $reviews = $this->em->getRepository(Review::class)->findAll();
        self::assertCount(1, $reviews);
        self::assertSame('Great service', $reviews[0]->getComment());
    }

    private function persistGroomer(): GroomerProfile
    {
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $groomer = new GroomerProfile($groomerUser, $city, 'Best Groomers', 'About');
        $groomer->refreshSlugFrom($groomer->getBusinessName());

        $this->em->persist($groomerUser);
        $this->em->persist($city);
        $this->em->persist($groomer);
        $this->em->flush();

        return $groomer;
    }
}
