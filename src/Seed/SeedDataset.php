<?php

declare(strict_types=1);

namespace App\Seed;

use App\Entity\User;

/**
 * @psalm-type CityData=array{name:string}
 * @psalm-type ServiceData=array{name:string}
 * @psalm-type UserData=array{email:string,password:string,roles:array<int,string>}
 * @psalm-type ReviewData=array{
 *     authorEmail:string,
 *     rating:int,
 *     comment:string,
 *     verified?:bool
 * }
 * @psalm-type GroomerProfileData=array{
 *     userEmail?:string|null,
 *     city:string,
 *     businessName:string,
 *     about:string,
 *     services:array<int,string>,
 *     priceRange?:string,
 *     badges?:array<int,string>,
 *     imagePath?:string,
 *     reviews?:array<int,ReviewData>
 * }
 */
final class SeedDataset
{
    /**
     * @param array<int,CityData>           $cities
     * @param array<int,ServiceData>        $services
     * @param array<int,UserData>           $users
     * @param array<int,GroomerProfileData> $groomerProfiles
     */
    public function __construct(
        public readonly array $cities,
        public readonly array $services,
        public readonly array $users,
        public readonly array $groomerProfiles,
    ) {
    }

    public static function default(): self
    {
        return new self(
            cities: [
                ['name' => 'Sofia'],
                ['name' => 'Plovdiv'],
                ['name' => 'Varna'],
                ['name' => 'Burgas'],
                ['name' => 'Ruse'],
            ],
            services: [
                ['name' => 'Mobile Dog Grooming'],
            ],
            users: [
                ['email' => 'groomer1@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer2@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer3@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer4@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer5@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'rusegroomer1@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'rusegroomer2@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'owner@example.com', 'password' => 'hash', 'roles' => [User::ROLE_PET_OWNER]],
            ],
            groomerProfiles: [
                [
                    'userEmail' => 'groomer1@example.com',
                    'city' => 'Sofia',
                    'businessName' => 'Sofia Mobile Groomer',
                    'about' => 'Professional mobile grooming services.',
                    'services' => ['Mobile Dog Grooming'],
                ],
                [
                    'userEmail' => 'groomer2@example.com',
                    'city' => 'Plovdiv',
                    'businessName' => 'Plovdiv Mobile Groomer',
                    'about' => 'Professional mobile grooming services.',
                    'services' => ['Mobile Dog Grooming'],
                ],
                [
                    'userEmail' => 'groomer3@example.com',
                    'city' => 'Varna',
                    'businessName' => 'Varna Mobile Groomer',
                    'about' => 'Professional mobile grooming services.',
                    'services' => ['Mobile Dog Grooming'],
                ],
                [
                    'userEmail' => 'groomer4@example.com',
                    'city' => 'Burgas',
                    'businessName' => 'Burgas Mobile Groomer',
                    'about' => 'Professional mobile grooming services.',
                    'services' => ['Mobile Dog Grooming'],
                ],
                [
                    'userEmail' => 'groomer5@example.com',
                    'city' => 'Ruse',
                    'businessName' => 'Ruse Mobile Groomer',
                    'about' => 'Professional mobile grooming services.',
                    'services' => ['Mobile Dog Grooming'],
                ],
                [
                    'userEmail' => 'rusegroomer1@example.com',
                    'city' => 'Ruse',
                    'businessName' => 'Ruse Waggin Wheels',
                    'about' => 'Mobile grooming with care for every pup.',
                    'services' => ['Mobile Dog Grooming'],
                    'priceRange' => '40 BGN',
                    'badges' => ['Verified'],
                    'imagePath' => 'uploads/seed/groomers/paw.svg',
                    'reviews' => [
                        ['authorEmail' => 'owner@example.com', 'rating' => 3, 'comment' => 'Good service', 'verified' => true],
                        ['authorEmail' => 'owner@example.com', 'rating' => 3, 'comment' => 'Decent grooming'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Nice work'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 3, 'comment' => 'Okay overall'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 3, 'comment' => 'Average experience'],
                    ],
                ],
                [
                    'userEmail' => 'rusegroomer2@example.com',
                    'city' => 'Ruse',
                    'businessName' => 'Danube Dog Stylists',
                    'about' => 'Stylish cuts by the Danube.',
                    'services' => ['Mobile Dog Grooming'],
                    'priceRange' => '55 BGN',
                    'badges' => ['Verified'],
                    'imagePath' => 'uploads/seed/groomers/paw.svg',
                    'reviews' => [
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Reliable grooming'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Friendly staff'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Pups look great'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Consistent service'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Quality trims'],
                    ],
                ],
                [
                    'city' => 'Ruse',
                    'businessName' => 'Ruse Pup Pamperers',
                    'about' => 'Pampering pups across Ruse.',
                    'services' => ['Mobile Dog Grooming'],
                    'priceRange' => '70 BGN',
                    'imagePath' => 'uploads/seed/groomers/paw.svg',
                    'reviews' => [
                        ['authorEmail' => 'owner@example.com', 'rating' => 5, 'comment' => 'Fantastic'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 5, 'comment' => 'Loved it'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 5, 'comment' => 'Top notch'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Very good'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 5, 'comment' => 'Excellent'],
                    ],
                ],
                [
                    'city' => 'Ruse',
                    'businessName' => 'Ruse Rover Revamps',
                    'about' => 'Revamping rovers on wheels.',
                    'services' => ['Mobile Dog Grooming'],
                    'priceRange' => '45 BGN',
                    'imagePath' => 'uploads/seed/groomers/paw.svg',
                    'reviews' => [
                        ['authorEmail' => 'owner@example.com', 'rating' => 5, 'comment' => 'Outstanding'],
                    ],
                ],
                [
                    'city' => 'Ruse',
                    'businessName' => 'Mobile Mutts Ruse',
                    'about' => 'Convenient grooming for busy owners.',
                    'services' => ['Mobile Dog Grooming'],
                    'priceRange' => '60 BGN',
                    'imagePath' => 'uploads/seed/groomers/paw.svg',
                ],
                [
                    'city' => 'Ruse',
                    'businessName' => 'Ruse Tail Trimmers',
                    'about' => 'Trimming tails with precision.',
                    'services' => ['Mobile Dog Grooming'],
                    'priceRange' => '50 BGN',
                    'imagePath' => 'uploads/seed/groomers/paw.svg',
                    'reviews' => [
                        ['authorEmail' => 'owner@example.com', 'rating' => 5, 'comment' => 'Great job'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Happy dog'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Will book again'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Nice team'],
                        ['authorEmail' => 'owner@example.com', 'rating' => 4, 'comment' => 'Good value'],
                    ],
                ],
            ],
        );
    }
}
