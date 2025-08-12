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

final class GroomerDetailRenderTest extends WebTestCase
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

    public function testDetailShowsOptionalFields(): void
    {
        $user = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $profile = new GroomerProfile($user, $city, 'Best Groomers', 'About us');
        $profile->refreshSlugFrom($profile->getBusinessName());
        $profile->setServiceArea('Downtown');
        $profile->setPhone('123-456');
        $profile->setServicesOffered('Bathing');
        $profile->setPriceRange('$$');

        $this->em->persist($user);
        $this->em->persist($city);
        $this->em->persist($profile);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$profile->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Downtown', $content);
        self::assertStringContainsString('123-456', $content);
        self::assertStringContainsString('Bathing', $content);
        self::assertStringContainsString('$$', $content);
    }
}
