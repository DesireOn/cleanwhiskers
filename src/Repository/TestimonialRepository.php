<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Testimonial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Testimonial>
 */
class TestimonialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Testimonial::class);
    }

    /**
     * @return list<Testimonial>
     */
    public function findAllOrderedByDate(): array
    {
        /** @var list<Testimonial> $result */
        $result = $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
