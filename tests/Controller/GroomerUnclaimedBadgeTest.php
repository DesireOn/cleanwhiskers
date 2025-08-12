<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GroomerUnclaimedBadgeTest extends WebTestCase
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

    public function testUnclaimedProfileShowsBadge(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);

        $profile = new GroomerProfile(null, $city, 'Unclaimed Groomer', 'About');
        $profile->refreshSlugFrom($profile->getBusinessName());
        $this->em->persist($profile);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$profile->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Unclaimed profile', $content);
        self::assertStringContainsString('/register', $content);
    }

    public function testClaimedProfileHidesBadge(): void
    {
        $city = new City('Varna');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);

        $user = (new User())
            ->setEmail('claimed@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $profile = new GroomerProfile($user, $city, 'Claimed Groomer', 'About');
        $profile->refreshSlugFrom($profile->getBusinessName());
        $this->em->persist($user);
        $this->em->persist($profile);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$profile->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringNotContainsString('Unclaimed profile', $content);
    }
}
