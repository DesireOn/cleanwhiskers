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
        $cityNames = ['Sofia', 'Plovdiv', 'Varna', 'Burgas', 'Ruse'];
        $cities = [];
        foreach ($cityNames as $name) {
            $city = new City($name);
            $manager->persist($city);
            $cities[$name] = $city;
        }

        $service = (new Service())
            ->setName('Mobile Dog Grooming');
        $manager->persist($service);

        $petOwner = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_PET_OWNER]);
        $manager->persist($petOwner);

        foreach ($cityNames as $index => $name) {
            $groomerUser = (new User())
                ->setEmail(sprintf('groomer%d@example.com', $index + 1))
                ->setPassword('hash')
                ->setRoles([User::ROLE_GROOMER]);
            $manager->persist($groomerUser);

            $profile = new GroomerProfile(
                $groomerUser,
                $cities[$name],
                sprintf('%s Mobile Groomer', $name),
                'Professional mobile grooming services.',
            );
            $profile->addService($service);
            $manager->persist($profile);

            $review = new Review($profile, $petOwner, 5, 'Excellent service!');
            $manager->persist($review);

            $booking = (new BookingRequest($profile, $petOwner))
                ->setService($service);
            $manager->persist($booking);
        }

        $manager->flush();
    }
}
