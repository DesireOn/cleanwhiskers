<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Entity\City;
use App\Infrastructure\Doctrine\SlugListener;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class SlugListenerTest extends TestCase
{
    public function testGeneratesSlugWhenEmpty(): void
    {
        $cityRepo = $this->createMock(CityRepository::class);
        $qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $query = $this->getMockBuilder(Query::class)->disableOriginalConstructor()->getMock();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);
        $query->method('setMaxResults')->willReturn($query);
        $query->method('setHint')->willReturn($query);
        $query->method('getOneOrNullResult')->willReturn(null);
        $cityRepo->method('createQueryBuilder')->willReturn($qb);

        $listener = new SlugListener($cityRepo, $this->createMock(ServiceRepository::class), $this->createMock(GroomerProfileRepository::class));

        $city = new City('Sofia');
        $args = new PrePersistEventArgs($city, $this->createMock(EntityManagerInterface::class));

        $listener->prePersist($args);

        self::assertSame('sofia', $city->getSlug());
    }

    public function testThrowsOnCollision(): void
    {
        $existing = new City('Sofia', 'sofia');

        $cityRepo = $this->createMock(CityRepository::class);
        $qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $query = $this->getMockBuilder(Query::class)->disableOriginalConstructor()->getMock();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);
        $query->method('setMaxResults')->willReturn($query);
        $query->method('setHint')->willReturn($query);
        $query->method('getOneOrNullResult')->willReturn($existing);
        $cityRepo->method('createQueryBuilder')->willReturn($qb);

        $listener = new SlugListener($cityRepo, $this->createMock(ServiceRepository::class), $this->createMock(GroomerProfileRepository::class));

        $city = new City('Sofia');
        $args = new PrePersistEventArgs($city, $this->createMock(EntityManagerInterface::class));

        $this->expectException(\InvalidArgumentException::class);
        $listener->prePersist($args);
    }
}
