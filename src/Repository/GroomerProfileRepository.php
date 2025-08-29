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
     * Find groomers by city + service, with sorting and pagination.
     *
     * @return Paginator<GroomerProfile>
     */
    public function findByFilters(
        City $city,
        Service $service,
        string $sort = 'recommended',
        int $page = 1,
        int $perPage = 12,
    ): Paginator {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $qb = $this->createQueryBuilder('g')
            ->innerJoin('g.services', 's')
            ->where('g.city = :city')
            ->andWhere('s = :service')
            ->setParameter('city', $city)
            ->setParameter('service', $service)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $sort = in_array($sort, ['recommended', 'price_asc', 'rating_desc'], true) ? $sort : 'recommended';

        if ('price_asc' === $sort) {
            // Nulls last, then ascending numeric price
            $qb->addSelect('CASE WHEN g.price IS NULL THEN 1 ELSE 0 END AS HIDDEN price_nulls')
               ->addOrderBy('price_nulls', 'ASC')
               ->addOrderBy('g.price', 'ASC')
               ->addOrderBy('g.id', 'ASC');
        } else {
            // rating-based sorts using scalar subqueries (avoids GROUP BY issues on MySQL)
            $avgSubDql = '(SELECT AVG(r2.rating) FROM '.Review::class.' r2 WHERE r2.groomer = g)';
            $countSubDql = '(SELECT COUNT(r3.id) FROM '.Review::class.' r3 WHERE r3.groomer = g)';

            $qb->addSelect($avgSubDql.' AS HIDDEN avg_rating')
               ->addSelect($countSubDql.' AS HIDDEN reviews_count')
               // Prefer profiles with attached users as a proxy for verification
               ->addSelect('CASE WHEN g.user IS NULL THEN 0 ELSE 1 END AS HIDDEN user_score');

            if ('rating_desc' === $sort) {
                $qb->addOrderBy('avg_rating', 'DESC')
                   ->addOrderBy('reviews_count', 'DESC')
                   ->addOrderBy('g.id', 'DESC');
            } else { // recommended
                $qb->addOrderBy('avg_rating', 'DESC')
                   ->addOrderBy('user_score', 'DESC')
                   ->addOrderBy('reviews_count', 'DESC')
                   ->addOrderBy('g.id', 'DESC');
            }
        }

        return new Paginator($qb->getQuery());
    }

    /**
     * @return Paginator<GroomerProfile>
     */
    public function findByCitySlug(string $slug, int $page = 1): Paginator
    {
        $limit = 12;
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
