<?php

declare(strict_types=1);

namespace App\Repository\Blog;

use App\Entity\Blog\BlogCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogCategory>
 */
class BlogCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogCategory::class);
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
     * @return iterable<int, array{slug: string}>
     */
    public function findAllSlugs(): iterable
    {
        /** @var iterable<int, array{slug: string}> $result */
        $result = $this->createQueryBuilder('c')
            ->select('c.slug AS slug')
            ->orderBy('c.slug', 'ASC')
            ->getQuery()
            ->toIterable();

        return $result;
    }
}
