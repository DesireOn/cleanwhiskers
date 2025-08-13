<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findOneBySlug(string $slug): ?City
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function existsBySlug(string $slug): bool
    {
        return null !== $this->createQueryBuilder('c')
            ->select('1')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return City[]
     */
    public function findTop(int $limit = 6): array
    {
        /** @var City[] $cities */
        $cities = $this->createQueryBuilder('c')
            ->select('partial c.{id, name, slug, seoIntro}')
            ->orderBy('c.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $cities;
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
        $rows = $this->createQueryBuilder('c')
            ->select('c.name', 'c.slug')
            ->where('LOWER(c.name) LIKE :q')
            ->setParameter('q', '%'.$q.'%')
            ->orderBy('c.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }
}
