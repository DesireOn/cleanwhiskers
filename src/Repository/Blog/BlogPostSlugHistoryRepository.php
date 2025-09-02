<?php

declare(strict_types=1);

namespace App\Repository\Blog;

use App\Entity\Blog\BlogPost;
use App\Entity\Blog\BlogPostSlugHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPostSlugHistory>
 */
class BlogPostSlugHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPostSlugHistory::class);
    }

    public function existsBySlug(string $slug): bool
    {
        return null !== $this->createQueryBuilder('h')
            ->select('1')
            ->where('h.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublishedPostBySlug(string $slug): ?BlogPost
    {
        /** @var BlogPostSlugHistory|null $history */
        $history = $this->createQueryBuilder('h')
            ->join('h.post', 'p')
            ->addSelect('p')
            ->andWhere('h.slug = :slug')
            ->andWhere('p.isPublished = 1')
            ->andWhere('p.publishedAt <= :now')
            ->setParameter('slug', $slug)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();

        return $history?->getPost();
    }
}
