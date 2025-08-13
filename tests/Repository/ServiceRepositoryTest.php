<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ServiceRepositoryTest extends KernelTestCase
{
    private ServiceRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->repository = $container->get(ServiceRepository::class);
        $this->em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testFindByNameLikeReturnsExpectedItems(): void
    {
        $dog = (new Service())->setName('Dog Grooming');
        $dog->refreshSlugFrom($dog->getName());
        $cat = (new Service())->setName('Cat Grooming');
        $cat->refreshSlugFrom($cat->getName());
        $this->em->persist($dog);
        $this->em->persist($cat);
        $this->em->flush();

        $results = $this->repository->findByNameLike(' dog ');

        self::assertSame([
            ['name' => 'Dog Grooming', 'slug' => 'dog-grooming'],
        ], $results);
    }

    public function testFindByNameLikeRespectsLimit(): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $service = (new Service())->setName('Service '.$i);
            $service->refreshSlugFrom($service->getName());
            $this->em->persist($service);
        }
        $this->em->flush();

        $results = $this->repository->findByNameLike('service', 5);

        self::assertCount(5, $results);
    }
}
