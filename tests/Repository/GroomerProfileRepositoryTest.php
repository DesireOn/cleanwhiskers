<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\GroomerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GroomerProfileRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private GroomerProfileRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(GroomerProfile::class);
    }

    public function testFindByFiltersSortingRecommendedAndRating(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $u1 = (new User())->setEmail('a@example.com')->setRoles([User::ROLE_GROOMER])->setPassword('hash');
        $u2 = (new User())->setEmail('b@example.com')->setRoles([User::ROLE_GROOMER])->setPassword('hash');

        $p1 = new GroomerProfile($u1, $city, 'Alpha', 'About'); // higher rating, has user
        $p1->refreshSlugFrom($p1->getBusinessName());
        $p1->addService($service);

        $p2 = new GroomerProfile(null, $city, 'Bravo', 'About'); // lower rating, no user
        $p2->refreshSlugFrom($p2->getBusinessName());
        $p2->addService($service);

        $p3 = new GroomerProfile($u2, $city, 'Charlie', 'About'); // mid rating, has user, more reviews
        $p3->refreshSlugFrom($p3->getBusinessName());
        $p3->addService($service);

        $author = (new User())->setEmail('author@example.com')->setPassword('hash');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($u1);
        $this->em->persist($u2);
        $this->em->persist($p1);
        $this->em->persist($p2);
        $this->em->persist($p3);
        $this->em->persist($author);
        $this->em->flush();

        // Ratings: p1=5 (1 rev), p2=3 (1 rev), p3=(4,4) -> 4 (2 revs)
        $this->em->persist(new Review($p1, $author, 5, 'Great'));
        $this->em->persist(new Review($p2, $author, 3, 'Ok'));
        $this->em->persist(new Review($p3, $author, 4, 'Good'));
        $this->em->persist(new Review($p3, $author, 4, 'Good again'));
        $this->em->flush();
        $this->em->clear();

        // Recommended: avg desc, user_score desc, reviews_count desc
        $recommended = $this->repository->findByFilters($city, $service, 'recommended', 1, 10);
        $names = array_map(fn($g) => $g->getBusinessName(), iterator_to_array($recommended));
        self::assertSame(['Alpha', 'Charlie', 'Bravo'], $names);

        // Rating desc: avg desc, then reviews_count desc
        $ratingDesc = $this->repository->findByFilters($city, $service, 'rating_desc', 1, 10);
        $names2 = array_map(fn($g) => $g->getBusinessName(), iterator_to_array($ratingDesc));
        self::assertSame(['Alpha', 'Charlie', 'Bravo'], $names2);
    }

    public function testFindByFiltersPriceAscNullsLast(): void
    {
        $city = new City('Varna');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Clip');
        $service->refreshSlugFrom($service->getName());

        $u = (new User())->setEmail('u@example.com')->setRoles([User::ROLE_GROOMER])->setPassword('hash');
        $a = new GroomerProfile($u, $city, 'A', 'About');
        $a->refreshSlugFrom($a->getBusinessName());
        $a->addService($service);
        $a->setPrice(10);

        $b = new GroomerProfile($u, $city, 'B', 'About');
        $b->refreshSlugFrom($b->getBusinessName());
        $b->addService($service);
        $b->setPrice(20);

        $c = new GroomerProfile($u, $city, 'C', 'About');
        $c->refreshSlugFrom($c->getBusinessName());
        $c->addService($service);
        // no price set

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($u);
        $this->em->persist($a);
        $this->em->persist($b);
        $this->em->persist($c);
        $this->em->flush();

        $p = $this->repository->findByFilters($city, $service, 'price_asc', 1, 10);
        $names = array_map(fn($g) => $g->getBusinessName(), iterator_to_array($p));
        self::assertSame(['A', 'B', 'C'], $names);
    }

    public function testFindFeaturedReturnsTopRatedProfiles(): void
    {
        $city = new City('Plovdiv');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Clipping');
        $service->refreshSlugFrom($service->getName());

        $topUser = (new User())
            ->setEmail('top@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $topProfile = new GroomerProfile($topUser, $city, 'Top', 'About top');
        $topProfile->refreshSlugFrom($topProfile->getBusinessName());
        $topProfile->addService($service);

        $midUser = (new User())
            ->setEmail('mid@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $midProfile = new GroomerProfile($midUser, $city, 'Mid', 'About mid');
        $midProfile->refreshSlugFrom($midProfile->getBusinessName());
        $midProfile->addService($service);

        $author = (new User())
            ->setEmail('rater@example.com')
            ->setPassword('hash');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($topUser);
        $this->em->persist($topProfile);
        $this->em->persist($midUser);
        $this->em->persist($midProfile);
        $this->em->persist($author);
        $this->em->flush();

        $this->em->persist(new Review($topProfile, $author, 5, 'Great'));
        $this->em->persist(new Review($midProfile, $author, 4, 'Good'));
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findFeatured(1);
        self::assertCount(1, $result);
        self::assertSame('Top', $result[0]['profile']->getBusinessName());
        self::assertSame(5.0, $result[0]['rating']);
        self::assertSame(1, $result[0]['reviewCount']);
    }

    public function testFindFeaturedRespectsLimit(): void
    {
        $city = new City('Burgas');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);

        $author = (new User())
            ->setEmail('author@example.com')
            ->setPassword('hash');
        $this->em->persist($author);

        for ($i = 0; $i < 5; ++$i) {
            $user = (new User())
                ->setEmail(sprintf('g%d@example.com', $i))
                ->setRoles([User::ROLE_GROOMER])
                ->setPassword('hash');
            $profile = new GroomerProfile($user, $city, 'Groomer '.$i, 'About');
            $profile->refreshSlugFrom($profile->getBusinessName());

            $this->em->persist($user);
            $this->em->persist($profile);
        }

        $this->em->flush();

        $profiles = $this->em->getRepository(GroomerProfile::class)->findAll();
        foreach ($profiles as $profile) {
            $this->em->persist(new Review($profile, $author, 5, 'Great'));
        }
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findFeatured(4);
        self::assertCount(4, $result);
    }
}
