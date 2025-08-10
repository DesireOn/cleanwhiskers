<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class GroomerProfileServicesTest extends TestCase
{
    public function testAddAndRemoveServices(): void
    {
        $user = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');

        $profile = new GroomerProfile($user, $city, 'Best Groomers', 'About');
        $profile->refreshSlugFrom($profile->getBusinessName());
        $service = (new Service())
            ->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $profile->addService($service);
        self::assertTrue($profile->getServices()->contains($service));

        $profile->removeService($service);
        self::assertFalse($profile->getServices()->contains($service));
    }
}
