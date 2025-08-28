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

            $profiles = [];
            foreach ($dataset->groomerProfiles as $profileData) {
                $user = null;
                if (!empty($profileData['userEmail']) && isset($users[$profileData['userEmail']])) {
                    $user = $users[$profileData['userEmail']];
                }
                $citySlug = $this->slugify($profileData['city']);
                $city = $cities[$citySlug] ?? null;
                if (null === $city) {
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
                }

                if (isset($profileData['priceRange'])) {
                    $profile->setPriceRange($profileData['priceRange']);
                }
                if (isset($profileData['badges'])) {
                    $profile->setBadges($profileData['badges']);
                }
                if (isset($profileData['imagePath'])) {
                    $profile->setImagePath($profileData['imagePath']);
                }

                if (isset($profileData['reviews'])) {
                    foreach ($profileData['reviews'] as $reviewData) {
                        $author = $users[$reviewData['authorEmail']] ?? null;
                        if (null === $author) {
                            continue;
                        }
                        $existingReview = $this->reviewRepository->findOneBy([
                            'groomer' => $profile,
                            'author' => $author,
                            'comment' => $reviewData['comment'],
                        ]);
                        if (null === $existingReview) {
                            $review = new Review($profile, $author, $reviewData['rating'], $reviewData['comment']);
                            if (!empty($reviewData['verified'])) {
                                $review->markVerified();
                            }
                            $this->em->persist($review);
                        }
                    }
                }

                $profiles[] = [
                    'entity' => $profile,
                    'hasReviews' => isset($profileData['reviews']),
                ];
            }

            if ($withSamples) {
                $petOwner = null;
                foreach ($users as $candidate) {
                    if (in_array(User::ROLE_PET_OWNER, $candidate->getRoles(), true)) {
                        $petOwner = $candidate;
                        break;
                    }
                }
                if (null !== $petOwner) {
                    foreach ($profiles as $profileInfo) {
                        $profile = $profileInfo['entity'];
                        if (!$profileInfo['hasReviews']) {
                            $existingReview = $this->reviewRepository->findOneBy([
                                'groomer' => $profile,
                                'author' => $petOwner,
                            ]);
                            if (null === $existingReview) {
                                $review = new Review($profile, $petOwner, 5, 'Sample review');
                                $this->em->persist($review);
                            }
                        }

                        $existingBooking = $this->bookingRequestRepository->findOneBy([
                            'groomer' => $profile,
                            'petOwner' => $petOwner,
                        ]);
                        if (null === $existingBooking) {
                            $booking = new BookingRequest($profile, $petOwner);
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
