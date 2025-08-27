<?php

declare(strict_types=1);

namespace App\DataFixtures\Seeder;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ServiceSeeder
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function seed(): void
    {
        if (null !== $this->serviceRepository->findMobileDogGroomingService()) {
            return;
        }

        $service = (new Service())
            ->setName('Mobile Dog Grooming');
        $service->refreshSlugFrom('Mobile Dog Grooming');

        $this->em->persist($service);
        $this->em->flush();
    }
}
