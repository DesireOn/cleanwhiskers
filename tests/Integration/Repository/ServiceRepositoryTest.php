<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

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
        $this->em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(Service::class);
    }

    public function testFindOneBySlug(): void
    {
        $service = (new Service())
            ->setName('Grooming');

        $this->em->persist($service);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneBySlug('grooming');

        self::assertNotNull($found);
        self::assertSame('Grooming', $found->getName());
    }

    public function testFindTopReturnsLimitedNumberOfServices(): void
    {
        for ($i = 1; $i <= 8; ++$i) {
            $service = (new Service())
                ->setName('Service '.$i);
            $service->refreshSlugFrom('Service '.$i);
            $this->em->persist($service);
        }

        $this->em->flush();

        $services = $this->repository->findTop(6);

        self::assertCount(6, $services);
    }

    public function testFindByNameLikeReturnsMatchingServices(): void
    {
        $grooming = (new Service())->setName('Grooming');
        $grooming->refreshSlugFrom('Grooming');
        $boarding = (new Service())->setName('Boarding');
        $boarding->refreshSlugFrom('Boarding');
        $this->em->persist($grooming);
        $this->em->persist($boarding);
        $this->em->flush();

        $results = $this->repository->findByNameLike('Gro', 8);

        self::assertCount(1, $results);
        self::assertSame('Grooming', $results[0]->getName());
    }

    public function testFindByNameLikeHonorsLimit(): void
    {
        for ($i = 1; $i <= 9; ++$i) {
            $service = (new Service())->setName('Test '.$i);
            $service->refreshSlugFrom('Test '.$i);
            $this->em->persist($service);
        }
        $this->em->flush();

        $results = $this->repository->findByNameLike('Test', 8);

        self::assertCount(8, $results);
    }
}
