<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use App\Entity\Blog\BlogTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPost>
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    public function existsBySlug(string $slug): bool
    {
        return null !== $this->createQueryBuilder('p')
            ->select('1')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return BlogPost[]
     */
    public function findPublished(): array
    {
        /** @var BlogPost[] $result */
        $result = $this->basePublishedQueryBuilder()
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return BlogPost[]
     */
    public function findPublishedByCategory(BlogCategory $category): array
    {
        /** @var BlogPost[] $result */
        $result = $this->basePublishedQueryBuilder()
            ->andWhere('p.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return BlogPost[]
     */
    public function findPublishedByTag(BlogTag $tag): array
    {
        /** @var BlogPost[] $result */
        $result = $this->basePublishedQueryBuilder()
            ->join('p.tags', 't')
            ->andWhere('t = :tag')
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getResult();

        return $result;
    }

    private function basePublishedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPublished = 1')
            ->andWhere('p.publishedAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.publishedAt', 'DESC');
    }
}
