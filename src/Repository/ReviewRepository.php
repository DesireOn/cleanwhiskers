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
}
