<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CityRepositoryTest extends KernelTestCase
{
    private CityRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->repository = $container->get(CityRepository::class);
        $this->em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testFindByNameLikeReturnsExpectedItems(): void
    {
        $ruse = new City('Ruse');
        $ruse->refreshSlugFrom($ruse->getName());
        $sofia = new City('Sofia');
        $sofia->refreshSlugFrom($sofia->getName());
        $this->em->persist($ruse);
        $this->em->persist($sofia);
        $this->em->flush();

        $results = $this->repository->findByNameLike(' Ru ');

        self::assertSame([
            ['name' => 'Ruse', 'slug' => 'ruse'],
        ], $results);
    }

    public function testFindByNameLikeRespectsLimit(): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $city = new City('City '.$i);
            $city->refreshSlugFrom($city->getName());
            $this->em->persist($city);
        }
        $this->em->flush();

        $results = $this->repository->findByNameLike('city', 3);

        self::assertCount(3, $results);
    }
}
