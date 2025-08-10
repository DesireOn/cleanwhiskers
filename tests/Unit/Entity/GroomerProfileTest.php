<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class GroomerProfileTest extends TestCase
{
    public function testConstructSetsProperties(): void
    {
        $user = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $profile = new GroomerProfile($user, $city, 'Best Groomers', 'About us');
        $profile->refreshSlugFrom($profile->getBusinessName());

        self::assertSame($user, $profile->getUser());
        self::assertSame($city, $profile->getCity());
        self::assertSame('Best Groomers', $profile->getBusinessName());
        self::assertSame('best-groomers', $profile->getSlug());
        self::assertSame('About us', $profile->getAbout());
    }

    public function testConstructorRejectsNonGroomerUser(): void
    {
        $user = (new User())
            ->setEmail('owner@example.com')
            ->setRoles([User::ROLE_PET_OWNER])
            ->setPassword('hash');
        $city = new City('Sofia');

        $this->expectException(\InvalidArgumentException::class);
        new GroomerProfile($user, $city, 'Biz', 'About');
    }
}
