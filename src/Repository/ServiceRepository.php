<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function findOneBySlug(string $slug): ?Service
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function existsBySlug(string $slug): bool
    {
        return null !== $this->createQueryBuilder('s')
            ->select('1')
            ->where('s.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Service[]
     */
    public function findTop(int $limit = 6): array
    {
        /** @var Service[] $services */
        $services = $this->createQueryBuilder('s')
            ->select('partial s.{id, name, slug}')
            ->orderBy('s.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $services;
    }

    /**
     * @return array<int, array{name: string, slug: string}>
     */
    public function findByNameLike(string $q, int $limit = 8): array
    {
        $q = trim(mb_strtolower($q));
        if ('' === $q) {
            return [];
        }

        /** @var array<int, array{name: string, slug: string}> $rows */
        $rows = $this->createQueryBuilder('s')
            ->select('s.name', 's.slug')
            ->where('LOWER(s.name) LIKE :q')
            ->setParameter('q', '%'.$q.'%')
            ->orderBy('s.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }
}
