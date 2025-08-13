<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\City;
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

    public function testFindByNameLikeReturnsMatchingCities(): void
    {
        $ruse = new City('Ruse');
        $ruse->refreshSlugFrom('Ruse');
        $sofia = new City('Sofia');
        $sofia->refreshSlugFrom('Sofia');
        $this->em->persist($ruse);
        $this->em->persist($sofia);
        $this->em->flush();

        $results = $this->repository->findByNameLike('Ru', 8);

        self::assertCount(1, $results);
        self::assertSame('Ruse', $results[0]->getName());
    }

    public function testFindByNameLikeHonorsLimit(): void
    {
        for ($i = 1; $i <= 9; ++$i) {
            $city = new City('Test '.$i);
            $city->refreshSlugFrom('Test '.$i);
            $this->em->persist($city);
        }
        $this->em->flush();

        $results = $this->repository->findByNameLike('Test', 8);

        self::assertCount(8, $results);
    }
}
