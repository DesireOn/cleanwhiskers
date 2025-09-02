<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GroomerProfile;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return list<Review>
     */
    public function findByGroomerProfileOrderedByDate(GroomerProfile $groomer): array
    {
        /** @var list<Review> $result */
        $result = $this->createQueryBuilder('r')
            ->andWhere('r.groomer = :groomer')
            ->setParameter('groomer', $groomer)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Return average rating and review count for a set of groomer IDs.
     *
     * @param int[] $groomerIds
     * @return array<int, array{avg: float|null, count: int}>
     */
    public function getAveragesForGroomers(array $groomerIds): array
    {
        if (0 === count($groomerIds)) {
            return [];
        }

        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.groomer) AS gid', 'AVG(r.rating) AS avgRating', 'COUNT(r.id) AS reviewsCount')
            ->where('r.groomer IN (:ids)')
            ->setParameter('ids', $groomerIds)
            ->groupBy('gid')
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $row) {
            $gid = (int) $row['gid'];
            $map[$gid] = [
                'avg' => null !== $row['avgRating'] ? (float) $row['avgRating'] : null,
                'count' => (int) $row['reviewsCount'],
            ];
        }

        return $map;
    }
}
