<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\City;
use App\Entity\LeadCapture;
use App\Entity\Service;
use App\Repository\LeadCaptureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LeadCaptureRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private LeadCaptureRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(LeadCapture::class);
    }

    public function testSavePersistsLead(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());
        $this->em->persist($city);
        $this->em->persist($service);

        $lead = new LeadCapture('John', 'john@example.com', $city, $service);
        $lead->setDogBreed('Labrador');

        $this->repository->save($lead);

        $results = $this->repository->findAll();
        self::assertCount(1, $results);
        self::assertSame('John', $results[0]->getName());
        self::assertSame('john@example.com', $results[0]->getEmail());
        self::assertSame('Labrador', $results[0]->getDogBreed());
    }
}
