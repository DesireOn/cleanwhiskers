<?php

declare(strict_types=1);

namespace App\Seed;

use App\Entity\BookingRequest;
use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\BookingRequestRepository;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ReviewRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class Seeder
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CityRepository $cityRepository,
        private readonly ServiceRepository $serviceRepository,
        private readonly UserRepository $userRepository,
        private readonly GroomerProfileRepository $profileRepository,
        private readonly ReviewRepository $reviewRepository,
        private readonly BookingRequestRepository $bookingRequestRepository,
    ) {
    }

    public function seed(SeedDataset $dataset, bool $withSamples = false): void
    {
        $this->em->wrapInTransaction(function () use ($dataset, $withSamples): void {
            $cities = [];
            foreach ($dataset->cities as $cityData) {
                $slug = $this->slugify($cityData['name']);
                $city = $this->cityRepository->findOneBySlug($slug);
                if (null === $city) {
                    $city = new City($cityData['name']);
                    $city->refreshSlugFrom($cityData['name']);
                    $this->em->persist($city);
                }
                $cities[$slug] = $city;
            }

            $services = [];
            foreach ($dataset->services as $serviceData) {
                $slug = $this->slugify($serviceData['name']);
                $service = $this->serviceRepository->findOneBySlug($slug);
                if (null === $service) {
                    $service = (new Service())->setName($serviceData['name']);
                    $service->refreshSlugFrom($serviceData['name']);
                    $this->em->persist($service);
                } else {
                    if ($service->getName() !== $serviceData['name']) {
                        $service->setName($serviceData['name']);
                        $service->refreshSlugFrom($serviceData['name']);
                    }
                }
                $services[$slug] = $service;
            }

            $users = [];
            foreach ($dataset->users as $userData) {
                $user = $this->userRepository->findOneBy(['email' => $userData['email']]);
                if (null === $user) {
                    $user = (new User())
                        ->setEmail($userData['email'])
                        ->setPassword($userData['password'])
                        ->setRoles($userData['roles']);
                    $this->em->persist($user);
                } else {
                    $user->setPassword($userData['password']);
                    $user->setRoles($userData['roles']);
                }
                $users[$userData['email']] = $user;
            }

            // Keep track of created profiles along with seed metadata
            $profiles = [];
            foreach ($dataset->groomerProfiles as $profileData) {
                $user = $users[$profileData['userEmail']] ?? null;
                $citySlug = $this->slugify($profileData['city']);
                $city = $cities[$citySlug] ?? null;
                if (null === $user || null === $city) {
                    continue; // skip invalid references
                }

                $slug = $this->slugify($profileData['businessName']);
                $profile = $this->profileRepository->findOneBySlug($slug);
                if (null === $profile) {
                    $profile = new GroomerProfile($user, $city, $profileData['businessName'], $profileData['about']);
                    $profile->refreshSlugFrom($profileData['businessName']);
                    foreach ($profileData['services'] as $serviceName) {
                        $serviceSlug = $this->slugify($serviceName);
                        if (isset($services[$serviceSlug])) {
                            $profile->addService($services[$serviceSlug]);
                        }
                    }
                    if (array_key_exists('price', $profileData)) {
                        $profile->setPrice(is_int($profileData['price']) ? $profileData['price'] : null);
                    }
                    $this->em->persist($profile);
                } else {
                    // ensure services
                    $desired = [];
                    foreach ($profileData['services'] as $serviceName) {
                        $desired[] = $this->slugify($serviceName);
                    }
                    foreach ($profile->getServices() as $existingService) {
                        if (!in_array($existingService->getSlug(), $desired, true)) {
                            $profile->removeService($existingService);
                        }
                    }
                    foreach ($desired as $serviceSlug) {
                        if (isset($services[$serviceSlug])) {
                            $profile->addService($services[$serviceSlug]);
                        }
                    }
                    if (array_key_exists('price', $profileData)) {
                        $profile->setPrice(is_int($profileData['price']) ? $profileData['price'] : null);
                    }
                }
                $profiles[] = [
                    'profile' => $profile,
                    'ratings' => $profileData['ratings'] ?? null,
                ];
            }

            if ($withSamples) {
                // collect pet owners to author sample reviews
                $petOwners = array_values(array_filter(
                    $users,
                    static fn (User $u): bool => in_array(User::ROLE_PET_OWNER, $u->getRoles(), true)
                ));

                if (count($petOwners) > 0) {
                    $ownerIndex = 0;
                    foreach ($profiles as $meta) {
                        /** @var GroomerProfile $profile */
                        $profile = $meta['profile'];
                        /** @var array<int,int>|null $ratings */
                        $ratings = $meta['ratings'];

                        if (is_array($ratings) && count($ratings) > 0) {
                            foreach ($ratings as $rating) {
                                $author = $petOwners[$ownerIndex % count($petOwners)];
                                $ownerIndex++;

                                $existingReview = $this->reviewRepository->findOneBy([
                                    'groomer' => $profile,
                                    'author' => $author,
                                ]);
                                if (null === $existingReview) {
                                    $review = new Review($profile, $author, $rating, sprintf('Sample %d-star review', $rating));
                                    $this->em->persist($review);
                                }
                            }
                        } else {
                            // Fallback: single 5-star sample with first owner
                            $author = $petOwners[0];
                            $existingReview = $this->reviewRepository->findOneBy([
                                'groomer' => $profile,
                                'author' => $author,
                            ]);
                            if (null === $existingReview) {
                                $review = new Review($profile, $author, 5, 'Sample review');
                                $this->em->persist($review);
                            }
                        }

                        // Also create a booking sample with the first owner for convenience
                        $existingBooking = $this->bookingRequestRepository->findOneBy([
                            'groomer' => $profile,
                            'petOwner' => $petOwners[0],
                        ]);
                        if (null === $existingBooking) {
                            $booking = new BookingRequest($profile, $petOwners[0]);
                            $service = $profile->getServices()->first();
                            if ($service instanceof Service) {
                                $booking->setService($service);
                            }
                            $this->em->persist($booking);
                        }
                    }
                }
            }

            $this->em->flush();
        });
    }

    private function slugify(string $source): string
    {
        $normalized = preg_replace('/\s+/', ' ', mb_strtolower(trim($source))) ?? '';

        return (new AsciiSlugger())->slug($normalized)->lower()->toString();
    }
}
