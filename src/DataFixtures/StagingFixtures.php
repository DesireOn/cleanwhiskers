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
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class StagingFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['staging'];
    }

    public function load(ObjectManager $manager): void
    {
        $cities = [];
        foreach (['Sofia', 'Plovdiv', 'Varna'] as $name) {
            $city = new City($name);
            $manager->persist($city);
            $cities[] = $city;
        }

        $services = [];
        foreach ([
            'Mobile Dog Grooming',
            'Cat Grooming',
            'Nail Trimming',
            'Full Groom',
        ] as $name) {
            $service = (new Service())->setName($name);
            $manager->persist($service);
            $services[] = $service;
        }

        $groomers = [];
        for ($i = 1; $i <= 5; ++$i) {
            $user = (new User())
                ->setEmail("groomer{$i}@example.com")
                ->setPassword('password')
                ->setRoles([User::ROLE_GROOMER]);
            $manager->persist($user);
            $groomers[] = $user;
        }

        $owners = [];
        for ($i = 1; $i <= 3; ++$i) {
            $user = (new User())
                ->setEmail("owner{$i}@example.com")
                ->setPassword('password')
                ->setRoles([User::ROLE_PET_OWNER]);
            $manager->persist($user);
            $owners[] = $user;
        }

        $profiles = [];
        for ($i = 0; $i < 5; ++$i) {
            $profile = new GroomerProfile(
                $groomers[$i],
                $cities[$i % count($cities)],
                "Groomer {$i} Biz",
                "About groomer {$i}",
            );
            $indexes = array_rand($services, random_int(1, 3));
            foreach ((array) $indexes as $idx) {
                $profile->addService($services[$idx]);
            }
            $manager->persist($profile);
            $profiles[] = $profile;
        }

        $reviewCount = random_int(6, 10);
        for ($i = 0; $i < $reviewCount; ++$i) {
            $review = new Review(
                $profiles[$i % count($profiles)],
                $owners[$i % count($owners)],
                random_int(3, 5),
                "Review {$i}",
            );
            $manager->persist($review);
        }

        $statuses = [
            BookingRequest::STATUS_PENDING,
            BookingRequest::STATUS_ACCEPTED,
            BookingRequest::STATUS_DECLINED,
        ];
        for ($i = 0; $i < 5; ++$i) {
            $booking = new BookingRequest(
                $profiles[$i % count($profiles)],
                $owners[$i % count($owners)],
            );
            $booking->setService($services[$i % count($services)]);
            $booking->setStatus($statuses[$i % count($statuses)]);
            $manager->persist($booking);
        }

        $manager->flush();
    }
}
