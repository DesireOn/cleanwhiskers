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

    /**
     * Fetches the most recent real testimonials, falling back to placeholders if needed.
     *
     * @return list<Testimonial>
     */
    public function findLatestWithFallback(int $limit): array
    {
        /** @var list<Testimonial> $real */
        $real = $this->createQueryBuilder('t')
            ->andWhere('t.isPlaceholder = :real')
            ->setParameter('real', false)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $remaining = $limit - count($real);
        if ($remaining <= 0) {
            return $real;
        }

        /** @var list<Testimonial> $placeholders */
        $placeholders = $this->createQueryBuilder('t')
            ->andWhere('t.isPlaceholder = :placeholder')
            ->setParameter('placeholder', true)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($remaining)
            ->getQuery()
            ->getResult();

        /** @var list<Testimonial> $merged */
        $merged = array_merge($real, $placeholders);

        return $merged;
    }
}
