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
 *     services:array<int,string>
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
            ],
        );
    }
}
