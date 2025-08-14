<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    /**
     * @return array<int, GroomerProfile>
     */
    public function findByFilters(
        City $city,
        Service $service,
        ?int $minRating = null,
        int $limit = 20,
        int $offset = 0,
    ): array {
        $qb = $this->createQueryBuilder('g')
            ->innerJoin('g.services', 's')
            ->where('g.city = :city')
            ->andWhere('s = :service')
            ->setParameter('city', $city)
            ->setParameter('service', $service)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (null !== $minRating) {
            $qb->leftJoin(Review::class, 'r', 'WITH', 'r.groomer = g')
                ->groupBy('g.id')
                ->having('AVG(r.rating) >= :minRating')
                ->setParameter('minRating', $minRating);
        }

        /** @var array<int, GroomerProfile> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @return Paginator<GroomerProfile>
     */
    public function findByCitySlug(string $slug, int $page = 1): Paginator
    {
        $limit = 20;
        $offset = max(0, ($page - 1) * $limit);

        $query = $this->createQueryBuilder('g')
            ->innerJoin('g.city', 'c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        /** @var Paginator<GroomerProfile> $paginator */
        $paginator = new Paginator($query);

        return $paginator;
    }

    /**
     * @return array<int, array{profile: GroomerProfile, rating: float, reviewCount: int}>
     */
    public function findFeatured(int $limit): array
    {
        $qb = $this->createQueryBuilder('g')
            ->select('g', 'AVG(r.rating) AS avgRating', 'COUNT(r.id) AS reviewCount')
            ->leftJoin(Review::class, 'r', 'WITH', 'r.groomer = g')
            ->where('g.user IS NOT NULL')
            ->groupBy('g.id')
            ->having('COUNT(r.id) > 0')
            ->orderBy('avgRating', 'DESC')
            ->addOrderBy('reviewCount', 'DESC')
            ->setMaxResults($limit);

        /** @var array<int, array{0: GroomerProfile, avgRating: string, reviewCount: string}> $rows */
        $rows = $qb->getQuery()->getResult();

        $result = [];
        foreach ($rows as $row) {
            $profile = $row[0];
            $result[] = [
                'profile' => $profile,
                'rating' => (float) $row['avgRating'],
                'reviewCount' => (int) $row['reviewCount'],
            ];
        }

        return $result;
    }
}
