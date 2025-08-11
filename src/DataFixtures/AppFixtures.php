<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\BookingRequest;
use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $city = new City('Sofia');
        $manager->persist($city);

        $service = (new Service())
            ->setName('Mobile Dog Grooming');
        $manager->persist($service);

        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $manager->persist($groomerUser);

        $petOwner = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_PET_OWNER]);
        $manager->persist($petOwner);

        $profile = new GroomerProfile(
            $groomerUser,
            $city,
            'Sofia Mobile Groomer',
            'Professional mobile grooming services.',
        );
        $profile->addService($service);
        $manager->persist($profile);

        $review = new Review($profile, $petOwner, 5, 'Excellent service!');
        $manager->persist($review);

        $booking = (new BookingRequest($profile, $petOwner))
            ->setService($service);
        $manager->persist($booking);

        $manager->flush();
    }
}
