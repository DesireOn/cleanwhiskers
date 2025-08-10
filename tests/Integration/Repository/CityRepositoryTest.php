<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CityRepositoryTest extends KernelTestCase
{
    public function testFindOneBySlugReturnsNullWhenNoCityExists(): void
    {
        self::bootKernel();

        /** @var CityRepository $repository */
        $repository = self::getContainer()->get(CityRepository::class);

        self::assertNull($repository->findOneBySlug('sofia'));
    }
}
