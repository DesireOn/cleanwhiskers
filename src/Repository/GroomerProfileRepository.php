<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroomerProfile>
 */
class GroomerProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroomerProfile::class);
    }

    public function findOneBySlug(string $slug): ?GroomerProfile
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function existsBySlug(string $slug): bool
    {
        return null !== $this->createQueryBuilder('g')
            ->select('1')
            ->where('g.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, GroomerProfile>
     */
    public function findByCityAndService(City $city, Service $service, int $limit = 20, int $offset = 0): array
    {
        $query = $this->createQueryBuilder('g')
            ->innerJoin('g.services', 's')
            ->where('g.city = :city')
            ->andWhere('s = :service')
            ->setParameter('city', $city)
            ->setParameter('service', $service)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        /** @var array<int, GroomerProfile> $result */
        $result = $query->getResult();

        return $result;
    }
}
