<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Domain\Shared\Exception\InvalidSlugSourceException;
use App\Domain\Shared\Exception\SlugCollisionException;
use App\Entity\City;
use App\Infrastructure\Doctrine\SlugListener;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PHPUnit\Framework\TestCase;

final class SlugListenerTest extends TestCase
{
    private CityRepository $cityRepository;
    private ServiceRepository $serviceRepository;
    private GroomerProfileRepository $groomerProfileRepository;
    private SlugListener $listener;

    protected function setUp(): void
    {
        $this->cityRepository = $this->createMock(CityRepository::class);
        $this->serviceRepository = $this->createMock(ServiceRepository::class);
        $this->groomerProfileRepository = $this->createMock(GroomerProfileRepository::class);

        $this->listener = new SlugListener(
            $this->cityRepository,
            $this->serviceRepository,
            $this->groomerProfileRepository
        );
    }

    public function testGeneratesSlugFromSourceWhenEmpty(): void
    {
        $city = new City('Sofia');
        $em = $this->createMock(EntityManagerInterface::class);
        $args = new PrePersistEventArgs($city, $em);

        $this->cityRepository->method('existsBySlug')->willReturn(false);

        $this->listener->prePersist($args);

        self::assertSame('sofia', $city->getSlug());
    }

    public function testThrowsSlugCollisionExceptionIfRepositoryReportsExistingSlug(): void
    {
        $city = new City('Sofia');
        $em = $this->createMock(EntityManagerInterface::class);
        $args = new PrePersistEventArgs($city, $em);

        $this->cityRepository->method('existsBySlug')->willReturn(true);

        $this->expectException(SlugCollisionException::class);
        $this->listener->prePersist($args);
    }

    public function testPreUpdateThrowsSlugCollisionExceptionIfSlugExists(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $city->refreshSlugFrom('Varna');
        $em = $this->createMock(EntityManagerInterface::class);
        $changeSet = ['slug' => ['sofia', 'varna']];
        $args = new PreUpdateEventArgs($city, $em, $changeSet);

        $this->cityRepository->method('existsBySlug')->willReturn(true);

        $this->expectException(SlugCollisionException::class);
        $this->listener->preUpdate($args);
    }

    public function testThrowsInvalidSlugSourceExceptionForEmptySource(): void
    {
        $city = new City('');
        $em = $this->createMock(EntityManagerInterface::class);
        $args = new PrePersistEventArgs($city, $em);

        $this->expectException(InvalidSlugSourceException::class);
        $this->listener->prePersist($args);
    }
}
