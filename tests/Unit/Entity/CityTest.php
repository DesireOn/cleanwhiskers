<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use PHPUnit\Framework\TestCase;

class CityTest extends TestCase
{
    public function testConstructSetsProperties(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());

        self::assertSame('Sofia', $city->getName());
        self::assertSame('sofia', $city->getSlug());
        self::assertInstanceOf(\DateTimeImmutable::class, $city->getCreatedAt());
    }
}
