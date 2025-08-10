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

        self::assertSame('Sofia', $city->getName());
        self::assertSame('', $city->getSlug());
        self::assertInstanceOf(\DateTimeImmutable::class, $city->getCreatedAt());
    }
}
