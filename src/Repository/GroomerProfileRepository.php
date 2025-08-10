<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GroomerProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GroomerProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroomerProfile::class);
    }

    /**
     * @return list<GroomerProfile>
     */
    public function findByCityAndService(string $citySlug, string $serviceSlug): array
    {
        return $this->createQueryBuilder('g')
            ->innerJoin('g.city', 'c')
            ->innerJoin('g.services', 's')
            ->andWhere('c.slug = :city')
            ->andWhere('s.slug = :service')
            ->setParameter('city', $citySlug)
            ->setParameter('service', $serviceSlug)
            ->getQuery()
            ->getResult();
    }
}
