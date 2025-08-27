<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CityRepositoryTest extends KernelTestCase
{
    private CityRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(City::class);
    }

    public function testFindOneBySlugReturnsNullWhenNoCityExists(): void
    {
        self::assertNull($this->repository->findOneBySlug('sofia'));
    }

    public function testFindOneBySlugReturnsCityWhenExists(): void
    {
        $city = new City('Sofia');
        $this->em->persist($city);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneBySlug('sofia');

        self::assertNotNull($found);
        self::assertSame('Sofia', $found->getName());
    }

    public function testFindTopReturnsLimitedNumberOfCities(): void
    {
        for ($i = 1; $i <= 8; ++$i) {
            $city = new City('City '.$i);
            $city->refreshSlugFrom('City '.$i);
            $this->em->persist($city);
        }

        $this->em->flush();

        $cities = $this->repository->findTop(6);

        self::assertCount(6, $cities);
    }

    public function testFindByServiceReturnsOnlyCitiesWithThatService(): void
    {
        $service = new Service();
        $service->setName('Mobile Dog Grooming');
        $service->refreshSlugFrom('Mobile Dog Grooming');
        $this->em->persist($service);

        $cityWith = new City('Sofia');
        $cityWith->refreshSlugFrom('Sofia');
        $this->em->persist($cityWith);

        $cityWithout = new City('Plovdiv');
        $cityWithout->refreshSlugFrom('Plovdiv');
        $this->em->persist($cityWithout);

        $groomer = new GroomerProfile(null, $cityWith, 'Biz', 'About');
        $groomer->getServices()->add($service);
        $this->em->persist($groomer);

        $this->em->flush();

        $cities = $this->repository->findByService($service);

        self::assertCount(1, $cities);
        self::assertSame('Sofia', $cities[0]->getName());
    }
}
