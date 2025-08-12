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

final class GroomerListByCityTest extends WebTestCase
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

    public function testListByCityShowsPaginatedProfiles(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $otherCity = new City('Plovdiv');
        $otherCity->refreshSlugFrom($otherCity->getName());
        $this->em->persist($city);
        $this->em->persist($otherCity);

        $firstProfile = null;
        for ($i = 1; $i <= 25; ++$i) {
            $user = (new User())
                ->setEmail('groomer'.$i.'@example.com')
                ->setRoles([User::ROLE_GROOMER])
                ->setPassword('hash');
            $profile = new GroomerProfile($user, $city, 'Groomer '.$i, 'About');
            $profile->refreshSlugFrom($profile->getBusinessName());
            if (null === $firstProfile) {
                $firstProfile = $profile;
            }
            $this->em->persist($user);
            $this->em->persist($profile);
        }

        $otherUser = (new User())
            ->setEmail('other@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $otherProfile = new GroomerProfile($otherUser, $otherCity, 'Other', 'About');
        $otherProfile->refreshSlugFrom($otherProfile->getBusinessName());
        $this->em->persist($otherUser);
        $this->em->persist($otherProfile);

        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Groomer 1', $content);
        self::assertStringNotContainsString('Other', $content);
        self::assertStringContainsString('/groomers/'.$firstProfile->getSlug(), $content);

        $this->client->request('GET', '/groomers/'.$city->getSlug().'?page=2');
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Groomer 25', $content);
        self::assertStringNotContainsString('Groomer 1', $content);
    }

    public function testUnknownCityReturns404(): void
    {
        $this->client->request('GET', '/groomers/unknown-city');
        self::assertResponseStatusCodeSame(404);
    }
}
