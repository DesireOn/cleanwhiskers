<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataFixtures\Seeder;

use App\DataFixtures\Seeder\ServiceSeeder;
use App\Entity\Service;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ServiceSeederTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ServiceSeeder $seeder;
    private ServiceRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(Service::class);
        $this->seeder = new ServiceSeeder($this->repository, $this->em);
    }

    public function testSeedCreatesMobileDogGroomingService(): void
    {
        self::assertNull($this->repository->findMobileDogGroomingService());

        $this->seeder->seed();

        $service = $this->repository->findMobileDogGroomingService();
        self::assertNotNull($service);
        self::assertSame('Mobile Dog Grooming', $service->getName());
    }
}
