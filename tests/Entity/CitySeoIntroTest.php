<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\City;
use PHPUnit\Framework\TestCase;

final class CitySeoIntroTest extends TestCase
{
    public function testSeoIntroGetterAndSetter(): void
    {
        $city = new City('Sofia');
        self::assertNull($city->getSeoIntro());

        $intro = 'Welcome to Sofia, the city of cats.';
        $city->setSeoIntro($intro);
        self::assertSame($intro, $city->getSeoIntro());
    }
}
