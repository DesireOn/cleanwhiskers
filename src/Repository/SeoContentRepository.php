<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\City;
use App\Entity\SeoContent;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SeoContent>
 */
class SeoContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeoContent::class);
    }

    public function findOneByCityAndService(City $city, Service $service): ?SeoContent
    {
        return $this->findOneBy([
            'city' => $city,
            'service' => $service,
        ]);
    }
}
