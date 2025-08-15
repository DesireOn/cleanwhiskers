<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Blog\BlogPost;
use App\Repository\Blog\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class BlogPostRepositoryTest extends TestCase
{
    public function testPaginationParametersApplied(): void
    {
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getArrayResult'])
            ->getMock();
        $query->expects(self::once())->method('getArrayResult')->willReturn([]);

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'select', 'join', 'andWhere', 'setParameter',
                'orderBy', 'setFirstResult', 'setMaxResults', 'getQuery'
            ])
            ->getMock();
        $qb->method('select')->willReturnSelf();
        $qb->method('join')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $qb->expects(self::once())->method('setFirstResult')->with(10)->willReturnSelf();
        $qb->expects(self::once())->method('setMaxResults')->with(5)->willReturnSelf();

        $metadata = new ClassMetadata(BlogPost::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getClassMetadata')->willReturn($metadata);
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->with(BlogPost::class)->willReturn($em);

        $repository = $this->getMockBuilder(BlogPostRepository::class)
            ->setConstructorArgs([$registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $repository->method('createQueryBuilder')->willReturn($qb);

        $repository->findPublished(3, 5);
    }
}
