<?php

declare(strict_types=1);

namespace App\Repository\Blog;

use App\Entity\Blog\BlogPost;
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
     * @return array<int, array<string, mixed>>
     */
    public function findPublished(int $page, int $perPage): array
    {
        $qb = $this->basePublishedQueryBuilder()
            ->setFirstResult(max(0, ($page - 1) * $perPage))
            ->setMaxResults($perPage);

        /** @var array<int, array<string, mixed>> $result */
        $result = $this->selectListFields($qb)
            ->getQuery()
            ->getArrayResult();

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findByCategorySlug(string $slug, int $page, int $perPage): array
    {
        $qb = $this->basePublishedQueryBuilder()
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->setFirstResult(max(0, ($page - 1) * $perPage))
            ->setMaxResults($perPage);

        /** @var array<int, array<string, mixed>> $result */
        $result = $this->selectListFields($qb)
            ->getQuery()
            ->getArrayResult();

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findByTagSlug(string $slug, int $page, int $perPage): array
    {
        $qb = $this->basePublishedQueryBuilder()
            ->join('p.tags', 't')
            ->andWhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->setFirstResult(max(0, ($page - 1) * $perPage))
            ->setMaxResults($perPage);

        /** @var array<int, array<string, mixed>> $result */
        $result = $this->selectListFields($qb)
            ->getQuery()
            ->getArrayResult();

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findLatest(int $limit): array
    {
        $qb = $this->basePublishedQueryBuilder()
            ->setMaxResults($limit);

        /** @var array<int, array<string, mixed>> $result */
        $result = $this->selectListFields($qb)
            ->getQuery()
            ->getArrayResult();

        return $result;
    }

    public function findOnePublishedBySlug(string $slug): ?BlogPost
    {
        /** @var BlogPost|null $result */
        $result = $this->createQueryBuilder('p')
            ->addSelect('c', 't')
            ->join('p.category', 'c')
            ->leftJoin('p.tags', 't')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.isPublished = 1')
            ->andWhere('p.publishedAt <= :now')
            ->setParameter('slug', $slug)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    private function basePublishedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->join('p.category', 'c')
            ->andWhere('p.isPublished = 1')
            ->andWhere('p.publishedAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.publishedAt', 'DESC');
    }

    private function selectListFields(QueryBuilder $qb): QueryBuilder
    {
        return $qb->select(
            'p.id AS id',
            'p.slug AS slug',
            'p.title AS title',
            'p.excerpt AS excerpt',
            'p.publishedAt AS publishedAt',
            'c.slug AS category_slug',
            'c.name AS category_name'
        );
    }
}
