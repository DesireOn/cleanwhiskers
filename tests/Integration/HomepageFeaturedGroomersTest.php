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

    public function testFeaturedGroomersSectionShowsAtMostFourProfiles(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $author = (new User())
            ->setEmail('author@example.com')
            ->setPassword('hash');

        $this->em->persist($city);
        $this->em->persist($author);

        for ($i = 0; $i < 5; ++$i) {
            $groomerUser = (new User())
                ->setEmail(sprintf('g%d@example.com', $i))
                ->setPassword('hash')
                ->setRoles([User::ROLE_GROOMER]);
            $profile = new GroomerProfile($groomerUser, $city, 'Biz '.$i, 'About');
            $profile->refreshSlugFrom($profile->getBusinessName());

            $this->em->persist($groomerUser);
            $this->em->persist($profile);
        }

        $this->em->flush();

        $profiles = $this->em->getRepository(GroomerProfile::class)->findAll();
        foreach ($profiles as $profile) {
            $this->em->persist(new Review($profile, $author, 5, 'Great'));
        }
        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSame(4, $crawler->filter('#featured-groomers .featured-groomer-card')->count());
    }
}
