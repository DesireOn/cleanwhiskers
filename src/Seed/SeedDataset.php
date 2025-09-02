<?php

declare(strict_types=1);

namespace App\Seed;

use App\Entity\User;

/**
 * @psalm-type CityData=array{name:string}
 * @psalm-type ServiceData=array{name:string}
 * @psalm-type UserData=array{email:string,password:string,roles:array<int,string>}
 * @psalm-type GroomerProfileData=array{
 *     userEmail:string,
 *     city:string,
 *     businessName:string,
 *     about:string,
 *     services:array<int,string>,
 *     price?:int,
 *     ratings?:array<int,int>,
 *     badges?:array<int,string>
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
                // Groomers
                ['email' => 'groomer1@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer2@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer3@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer4@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer5@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer6@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer7@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer8@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'groomer9@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                // Pet owners for generating varied reviews
                ['email' => 'owner1@example.com', 'password' => 'hash', 'roles' => [User::ROLE_PET_OWNER]],
                ['email' => 'owner2@example.com', 'password' => 'hash', 'roles' => [User::ROLE_PET_OWNER]],
                ['email' => 'owner3@example.com', 'password' => 'hash', 'roles' => [User::ROLE_PET_OWNER]],
                ['email' => 'owner4@example.com', 'password' => 'hash', 'roles' => [User::ROLE_PET_OWNER]],
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
                // Multiple groomers in Ruse with diverse prices and ratings
                [
                    'userEmail' => 'groomer5@example.com',
                    'city' => 'Ruse',
                    'businessName' => 'Ruse Budget Groomers',
                    'about' => 'Affordable grooming with great care.',
                    'services' => ['Mobile Dog Grooming'],
                    'price' => 25,
                    'ratings' => [5],
                    'badges' => ['Verified'],
                ],
                [
                    'userEmail' => 'groomer6@example.com',
                    'city' => 'Ruse',
                    'businessName' => 'Ruse Prime Grooming',
                    'about' => 'Premium mobile grooming for discerning owners.',
                    'services' => ['Mobile Dog Grooming'],
                    'price' => 60,
                    'ratings' => [5, 5, 4],
                    'badges' => ['Verified'],
                ],
                [
                    'userEmail' => 'groomer7@example.com',
                    'city' => 'Ruse',
                    'businessName' => 'Danube Paws Mobile',
                    'about' => 'Friendly service by the river.',
                    'services' => ['Mobile Dog Grooming'],
                    'price' => 45,
                    'ratings' => [3, 3],
                ],
                [
                    'userEmail' => 'groomer8@example.com',
                    'city' => 'Ruse',
                    'businessName' => 'QuickClip Ruse',
                    'about' => 'Fast and convenient nail trims and tidy-ups.',
                    'services' => ['Mobile Dog Grooming'],
                    'price' => 30,
                    'ratings' => [4],
                    'badges' => ['Verified'],
                ],
                [
                    'userEmail' => 'groomer9@example.com',
                    'city' => 'Ruse',
                    'businessName' => 'Ruse Groom & Shine',
                    'about' => 'Shiny coats and happy pets.',
                    'services' => ['Mobile Dog Grooming'],
                    // no price to ensure NULLs sort last for price_asc
                    'ratings' => [2, 1, 2],
                ],
            ],
        );
    }
}
