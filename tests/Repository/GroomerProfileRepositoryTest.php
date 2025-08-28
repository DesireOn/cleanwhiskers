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

    public function testFindByFiltersReturnsProfilesAboveRating(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $highUser = (new User())
            ->setEmail('high@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $highProfile = new GroomerProfile($highUser, $city, 'High', 'About');
        $highProfile->refreshSlugFrom($highProfile->getBusinessName());
        $highProfile->addService($service);

        $lowUser = (new User())
            ->setEmail('low@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $lowProfile = new GroomerProfile($lowUser, $city, 'Low', 'About');
        $lowProfile->refreshSlugFrom($lowProfile->getBusinessName());
        $lowProfile->addService($service);

        $author = (new User())
            ->setEmail('author@example.com')
            ->setPassword('hash');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($highUser);
        $this->em->persist($highProfile);
        $this->em->persist($lowUser);
        $this->em->persist($lowProfile);
        $this->em->persist($author);
        $this->em->flush();

        $highReview = new Review($highProfile, $author, 5, 'Great');
        $lowReview = new Review($lowProfile, $author, 3, 'Okay');
        $this->em->persist($highReview);
        $this->em->persist($lowReview);
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findByFilters($city, $service, 4);
        self::assertCount(1, $result);
        self::assertSame('High', $result[0]->getBusinessName());
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

    public function testFindByFiltersSortsRecommendedByRatingThenVerified(): void
    {
        $city = new City('Varna');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Trim');
        $service->refreshSlugFrom($service->getName());
        $author = (new User())
            ->setEmail('author@example.com')
            ->setPassword('hash');

        $verifiedUser = (new User())
            ->setEmail('verified@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $verifiedHigh = new GroomerProfile($verifiedUser, $city, 'Verified High', 'About');
        $verifiedHigh->refreshSlugFrom($verifiedHigh->getBusinessName());
        $verifiedHigh->addService($service);

        $unverifiedHigh = new GroomerProfile(null, $city, 'Unverified High', 'About');
        $unverifiedHigh->refreshSlugFrom($unverifiedHigh->getBusinessName());
        $unverifiedHigh->addService($service);

        $lowUser = (new User())
            ->setEmail('low@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $verifiedLow = new GroomerProfile($lowUser, $city, 'Verified Low', 'About');
        $verifiedLow->refreshSlugFrom($verifiedLow->getBusinessName());
        $verifiedLow->addService($service);

        $noReviewUser = (new User())
            ->setEmail('noreview@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $noReview = new GroomerProfile($noReviewUser, $city, 'No Review', 'About');
        $noReview->refreshSlugFrom($noReview->getBusinessName());
        $noReview->addService($service);

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($author);
        $this->em->persist($verifiedUser);
        $this->em->persist($verifiedHigh);
        $this->em->persist($unverifiedHigh);
        $this->em->persist($lowUser);
        $this->em->persist($verifiedLow);
        $this->em->persist($noReviewUser);
        $this->em->persist($noReview);
        $this->em->flush();

        $this->em->persist(new Review($verifiedHigh, $author, 5, 'Great'));
        $this->em->persist(new Review($unverifiedHigh, $author, 5, 'Great'));
        $this->em->persist(new Review($verifiedLow, $author, 3, 'Ok'));
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findByFilters($city, $service, null, 20, 0, 'recommended');

        self::assertSame('Verified High', $result[0]->getBusinessName());
        self::assertSame('Unverified High', $result[1]->getBusinessName());
        self::assertSame('Verified Low', $result[2]->getBusinessName());
        self::assertSame('No Review', $result[3]->getBusinessName());
    }

    public function testFindByFiltersSortsByPriceAscending(): void
    {
        $city = new City('Gabrovo');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $cheapUser = (new User())
            ->setEmail('cheap@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $cheap = new GroomerProfile($cheapUser, $city, 'Cheap', 'About');
        $cheap->refreshSlugFrom($cheap->getBusinessName());
        $cheap->addService($service);
        $cheap->setPriceRange('10');

        $expensiveUser = (new User())
            ->setEmail('expensive@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $expensive = new GroomerProfile($expensiveUser, $city, 'Expensive', 'About');
        $expensive->refreshSlugFrom($expensive->getBusinessName());
        $expensive->addService($service);
        $expensive->setPriceRange('30');

        $noPriceUser = (new User())
            ->setEmail('noprice@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $noPrice = new GroomerProfile($noPriceUser, $city, 'No Price', 'About');
        $noPrice->refreshSlugFrom($noPrice->getBusinessName());
        $noPrice->addService($service);

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($cheapUser);
        $this->em->persist($cheap);
        $this->em->persist($expensiveUser);
        $this->em->persist($expensive);
        $this->em->persist($noPriceUser);
        $this->em->persist($noPrice);
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findByFilters($city, $service, null, 20, 0, 'price_asc');

        self::assertSame('Cheap', $result[0]->getBusinessName());
        self::assertSame('Expensive', $result[1]->getBusinessName());
        self::assertSame('No Price', $result[2]->getBusinessName());
    }

    public function testFindByFiltersSortsByRatingDescending(): void
    {
        $city = new City('Ruse');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Clip');
        $service->refreshSlugFrom($service->getName());
        $author = (new User())
            ->setEmail('author2@example.com')
            ->setPassword('hash');

        $highUser = (new User())
            ->setEmail('high2@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $high = new GroomerProfile($highUser, $city, 'High', 'About');
        $high->refreshSlugFrom($high->getBusinessName());
        $high->addService($service);

        $lowUser = (new User())
            ->setEmail('low2@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $low = new GroomerProfile($lowUser, $city, 'Low', 'About');
        $low->refreshSlugFrom($low->getBusinessName());
        $low->addService($service);

        $noneUser = (new User())
            ->setEmail('none@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $none = new GroomerProfile($noneUser, $city, 'None', 'About');
        $none->refreshSlugFrom($none->getBusinessName());
        $none->addService($service);

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($author);
        $this->em->persist($highUser);
        $this->em->persist($high);
        $this->em->persist($lowUser);
        $this->em->persist($low);
        $this->em->persist($noneUser);
        $this->em->persist($none);
        $this->em->flush();

        $this->em->persist(new Review($high, $author, 5, 'Great'));
        $this->em->persist(new Review($low, $author, 3, 'Ok'));
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findByFilters($city, $service, null, 20, 0, 'rating_desc');

        self::assertSame('High', $result[0]->getBusinessName());
        self::assertSame('Low', $result[1]->getBusinessName());
        self::assertSame('None', $result[2]->getBusinessName());
    }
}
