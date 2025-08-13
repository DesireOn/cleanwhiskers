<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomepageFeaturedGroomersTest extends WebTestCase
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

    public function testFeaturedGroomersSectionRendersProfiles(): void
    {
        $city = new City('Sofia');
        $groomerUser = (new User())
            ->setEmail('g@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $author = (new User())
            ->setEmail('a@example.com')
            ->setPassword('hash');
        $profile = new GroomerProfile($groomerUser, $city, 'Fancy Groomers', 'About');
        $profile->setPriceRange('$$');

        $this->em->persist($city);
        $this->em->persist($groomerUser);
        $this->em->persist($author);
        $this->em->persist($profile);
        $this->em->persist(new Review($profile, $author, 5, 'Great'));
        $this->em->flush();

        $slug = $profile->getSlug();
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists(sprintf('#featured-groomers a[href="/groomers/%s"]', $slug));
        self::assertSelectorTextContains('#featured-groomers', 'Fancy Groomers');
    }
}
