<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lead>
 */
class LeadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lead::class);
    }

    /**
     * @return array<int, Lead>
     */
    public function findPendingByCityAndService(City $city, Service $service, int $limit = 50): array
    {
        /** @var array<int, Lead> $rows */
        $rows = $this->createQueryBuilder('l')
            ->where('l.city = :city')
            ->andWhere('l.service = :service')
            ->andWhere('l.status = :status')
            ->setParameter('city', $city)
            ->setParameter('service', $service)
            ->setParameter('status', Lead::STATUS_PENDING)
            ->setMaxResults($limit)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $rows;
    }

    public function findOneByOwnerTokenHash(string $hash): ?Lead
    {
        return $this->findOneBy(['ownerTokenHash' => $hash]);
    }
}

