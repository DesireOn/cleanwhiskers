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
}
