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
            ->setName('Grooming')
            ->setSlug('grooming');

        $this->em->persist($service);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneBySlug('grooming');

        self::assertNotNull($found);
        self::assertSame('Grooming', $found->getName());
    }
}
