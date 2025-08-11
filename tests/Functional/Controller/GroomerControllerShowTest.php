<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class GroomerControllerShowTest extends WebTestCase
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

    public function testShowDisplaysGroomer(): void
    {
        $user = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $profile = new GroomerProfile($user, $city, 'Best Groomers', 'About us');
        $profile->refreshSlugFrom($profile->getBusinessName());

        $this->em->persist($user);
        $this->em->persist($city);
        $this->em->persist($profile);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$profile->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Best Groomers', $content);
        self::assertStringContainsString('Sofia', $content);
    }

    public function testUnknownSlugReturns404(): void
    {
        $this->client->request('GET', '/groomers/unknown');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
