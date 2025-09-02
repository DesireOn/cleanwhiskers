<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\GroomerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GroomerProfileRepositoryTest extends KernelTestCase
{
    private GroomerProfileRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(GroomerProfile::class);
    }

    public function testFindOneBySlug(): void
    {
        $user = (new User())
            ->setEmail('groomer@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $city = new City('Sofia');

        $this->em->persist($user);
        $this->em->persist($city);
        $this->em->flush();

        $profile = new GroomerProfile($user, $city, 'Best Groomers', 'About us');
        $this->em->persist($profile);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneBySlug('best-groomers');
        self::assertNotNull($found);
        self::assertSame('Best Groomers', $found->getBusinessName());
    }

    public function testFindFeaturedReturnsClaimedProfilesOrderedByRatingAndVolume(): void
    {
        $city = new City('Sofia');
        $author = (new User())
            ->setEmail('author@example.com')
            ->setPassword('hash');

        $profiles = [];
        foreach (['Alpha', 'Beta', 'Gamma'] as $i => $name) {
            $user = (new User())
                ->setEmail(sprintf('groomer%d@example.com', $i))
                ->setPassword('hash')
                ->setRoles([User::ROLE_GROOMER]);
            $profile = new GroomerProfile($user, $city, $name, 'About');
            $profiles[$name] = $profile;
            $this->em->persist($user);
            $this->em->persist($profile);
        }
        $unclaimed = new GroomerProfile(null, $city, 'Delta', 'About');
        $this->em->persist($city);
        $this->em->persist($author);
        $this->em->persist($unclaimed);
        $this->em->flush();

        // Alpha: rating 5 with 3 reviews
        for ($i = 0; $i < 3; ++$i) {
            $this->em->persist(new Review($profiles['Alpha'], $author, 5, 'Great'));
        }
        // Beta: rating 4 with 5 reviews
        for ($i = 0; $i < 5; ++$i) {
            $this->em->persist(new Review($profiles['Beta'], $author, 4, 'Good'));
        }
        // Gamma: rating 4 with 2 reviews
        for ($i = 0; $i < 2; ++$i) {
            $this->em->persist(new Review($profiles['Gamma'], $author, 4, 'Okay'));
        }
        // Unclaimed profile reviews should be ignored
        $this->em->persist(new Review($unclaimed, $author, 5, 'Ignored'));
        $this->em->flush();

        $result = $this->repository->findFeatured(2);

        self::assertCount(2, $result);
        self::assertSame('Alpha', $result[0]['profile']->getBusinessName());
        self::assertSame(5.0, $result[0]['rating']);
        self::assertSame('Beta', $result[1]['profile']->getBusinessName());
        self::assertSame(5, $result[1]['reviewCount']);
    }
}
