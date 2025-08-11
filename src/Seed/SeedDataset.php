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
            ],
            services: [
                ['name' => 'Mobile Dog Grooming'],
            ],
            users: [
                ['email' => 'groomer@example.com', 'password' => 'hash', 'roles' => [User::ROLE_GROOMER]],
                ['email' => 'owner@example.com', 'password' => 'hash', 'roles' => [User::ROLE_PET_OWNER]],
            ],
            groomerProfiles: [
                [
                    'userEmail' => 'groomer@example.com',
                    'city' => 'Sofia',
                    'businessName' => 'Sofia Mobile Groomer',
                    'about' => 'Professional mobile grooming services.',
                    'services' => ['Mobile Dog Grooming'],
                ],
            ],
        );
    }
}
