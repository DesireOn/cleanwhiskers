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
}
