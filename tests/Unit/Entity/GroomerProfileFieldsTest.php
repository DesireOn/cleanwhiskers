<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class GroomerProfileFieldsTest extends TestCase
{
    public function testOptionalFieldsDefaultToNullAndCanBeSet(): void
    {
        $user = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $profile = new GroomerProfile($user, $city, 'Best Groomers', 'About us');

        self::assertNull($profile->getServiceArea());
        self::assertNull($profile->getPhone());
        self::assertNull($profile->getServicesOffered());
        self::assertNull($profile->getPriceRange());
        self::assertSame([], $profile->getBadges());
        self::assertSame([], $profile->getSpecialties());

        $profile->setServiceArea('Downtown');
        $profile->setPhone('123-456');
        $profile->setServicesOffered('Bathing');
        $profile->setPriceRange('$$');
        $profile->setBadges(['New']);
        $profile->setSpecialties(['Nail trimming']);

        self::assertSame('Downtown', $profile->getServiceArea());
        self::assertSame('123-456', $profile->getPhone());
        self::assertSame('Bathing', $profile->getServicesOffered());
        self::assertSame('$$', $profile->getPriceRange());
        self::assertSame(['New'], $profile->getBadges());
        self::assertSame(['Nail trimming'], $profile->getSpecialties());
    }
}
