<?php

declare(strict_types=1);

namespace App\Repository\Blog;

use App\Entity\Blog\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @extends ServiceEntityRepository<BlogPost>
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private TagAwareCacheInterface $cache)
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
        $cacheKey = sprintf('blog_published_%d_%d', $page, $perPage);

        /** @var array<int, array<string, mixed>> $result */
        $result = $this->cache->get($cacheKey, function (ItemInterface $item) use ($page, $perPage) {
            $item->expiresAfter(600);
            $item->tag('blog_posts');

            $qb = $this->basePublishedQueryBuilder()
                ->setFirstResult(max(0, ($page - 1) * $perPage))
                ->setMaxResults($perPage);

            return $this->selectListFields($qb)
                ->getQuery()
                ->getArrayResult();
        });

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findByCategorySlug(string $slug, int $page, int $perPage): array
    {
        $cacheKey = sprintf('blog_category_%s_%d_%d', $slug, $page, $perPage);

        /** @var array<int, array<string, mixed>> $result */
        $result = $this->cache->get($cacheKey, function (ItemInterface $item) use ($slug, $page, $perPage) {
            $item->expiresAfter(600);
            $item->tag('blog_posts');

            $qb = $this->basePublishedQueryBuilder()
                ->andWhere('c.slug = :slug')
                ->setParameter('slug', $slug)
                ->setFirstResult(max(0, ($page - 1) * $perPage))
                ->setMaxResults($perPage);

            return $this->selectListFields($qb)
                ->getQuery()
                ->getArrayResult();
        });

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findByTagSlug(string $slug, int $page, int $perPage): array
    {
        $cacheKey = sprintf('blog_tag_%s_%d_%d', $slug, $page, $perPage);

        /** @var array<int, array<string, mixed>> $result */
        $result = $this->cache->get($cacheKey, function (ItemInterface $item) use ($slug, $page, $perPage) {
            $item->expiresAfter(600);
            $item->tag('blog_posts');

            $qb = $this->basePublishedQueryBuilder()
                ->join('p.tags', 't')
                ->andWhere('t.slug = :slug')
                ->setParameter('slug', $slug)
                ->setFirstResult(max(0, ($page - 1) * $perPage))
                ->setMaxResults($perPage);

            return $this->selectListFields($qb)
                ->getQuery()
                ->getArrayResult();
        });

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findLatest(int $limit): array
    {
        $cacheKey = sprintf('blog_latest_%d', $limit);

        /** @var array<int, array<string, mixed>> $result */
        $result = $this->cache->get($cacheKey, function (ItemInterface $item) use ($limit) {
            $item->expiresAfter(600);
            $item->tag('blog_posts');

            $qb = $this->basePublishedQueryBuilder()
                ->setMaxResults($limit);

            return $this->selectListFields($qb)
                ->getQuery()
                ->getArrayResult();
        });

        return $result;
    }

    /**
     * @return iterable<int, array{slug: string, publishedAt: \DateTimeImmutable, updatedAt: \DateTimeImmutable}>
     */
    public function findAllForSitemap(): iterable
    {
        /** @var iterable<int, array{slug: string, publishedAt: \DateTimeImmutable, updatedAt: \DateTimeImmutable}> $result */
        $result = $this->createQueryBuilder('p')
            ->select('p.slug AS slug', 'p.publishedAt AS publishedAt', 'p.updatedAt AS updatedAt')
            ->andWhere('p.isPublished = 1')
            ->andWhere('p.publishedAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->toIterable();

        return $result;
    }

    /**
     * @return iterable<int, array{slug: string, title: string, excerpt: ?string, publishedAt: \DateTimeImmutable, updatedAt: \DateTimeImmutable}>
     */
    public function findLatestForFeed(int $limit): iterable
    {
        /**
         * @var iterable<int, array{slug: string, title: string, excerpt: ?string, publishedAt: \DateTimeImmutable, updatedAt: \DateTimeImmutable}> $result
         */
        $result = $this->createQueryBuilder('p')
            ->select(
                'p.slug AS slug',
                'p.title AS title',
                'p.excerpt AS excerpt',
                'p.publishedAt AS publishedAt',
                'p.updatedAt AS updatedAt'
            )
            ->andWhere('p.isPublished = 1')
            ->andWhere('p.publishedAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->toIterable();

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
            'p.updatedAt AS updatedAt',
            'c.slug AS category_slug',
            'c.name AS category_name'
        );
    }
}
