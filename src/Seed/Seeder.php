<?php

declare(strict_types=1);

namespace App\Seed;

use App\Entity\BookingRequest;
use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\EmailSuppression;
use App\Entity\AuditLog;
use App\Repository\AuditLogRepository;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\BookingRequestRepository;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ReviewRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Repository\EmailSuppressionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Lead\LeadTokenFactory;
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
        private readonly ?LeadTokenFactory $leadTokenFactory = null,
        private readonly ?EmailSuppressionRepository $emailSuppressionRepository = null,
        private readonly ?AuditLogRepository $auditLogs = null,
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
                    if (array_key_exists('badges', $profileData) && is_array($profileData['badges'])) {
                        /** @var array<int,string> $badges */
                        $badges = $profileData['badges'];
                        $profile->setBadges($badges);
                    }
                    // Provide outreach email for samples
                    if ($withSamples) {
                        $profile->setOutreachEmail('outreach+' . $this->slugify($profileData['businessName']) . '@example.com');
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
                    if (array_key_exists('badges', $profileData) && is_array($profileData['badges'])) {
                        /** @var array<int,string> $badges */
                        $badges = $profileData['badges'];
                        $profile->setBadges($badges);
                    }
                    if ($withSamples && $profile->getOutreachEmail() === null) {
                        $profile->setOutreachEmail('outreach+' . $profile->getSlug() . '@example.com');
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
                    foreach ($profiles as $meta) {
                        /** @var GroomerProfile $profile */
                        $profile = $meta['profile'];
                        /** @var array<int,int>|null $ratings */
                        $ratings = $meta['ratings'];

                        // Exactly one sample review per profile for idempotency
                        $author = $petOwners[0];
                        $rating = (is_array($ratings) && count($ratings) > 0) ? (int) $ratings[0] : 5;

                        $existingReview = $this->reviewRepository->findOneBy([
                            'groomer' => $profile,
                            'author' => $author,
                        ]);
                        if (null === $existingReview) {
                            $review = new Review($profile, $author, $rating, 'Sample review');
                            $this->em->persist($review);
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

                // Create a sample lead in Ruse for Mobile Dog Grooming
                $ruse = $cities['ruse'] ?? null;
                $mdg = $services['mobile-dog-grooming'] ?? null;
                if ($ruse instanceof City && $mdg instanceof Service) {
                    $lead = new Lead($ruse, $mdg, 'Jane Owner', 'jane.owner@example.com');
                    $lead->setPhone('555-0101');
                    $lead->setPetType('dog');
                    $lead->setBreedSize('medium');
                    $lead->setConsentToShare(true);
                    // Generate secure owner token and set expiry via factory if available
                    if ($this->leadTokenFactory) {
                        $this->leadTokenFactory->issueOwnerToken($lead);
                    }
                    $this->em->persist($lead);

                    // Two recipients from Ruse if available
                    $ruseGroomers = array_values(array_filter(
                        array_map(static fn(array $m) => $m['profile'], $profiles),
                        static fn(GroomerProfile $p): bool => $p->getCity()->getId() === $ruse->getId()
                    ));

                    $now = new \DateTimeImmutable();
                    for ($i = 0; $i < min(2, count($ruseGroomers)); $i++) {
                        $gp = $ruseGroomers[$i];
                        $email = $gp->getOutreachEmail() ?? ('groomer' . ($i+1) . '@example.com');
                        $recipient = new LeadRecipient($lead, $email, sha1('claim'.$i), $now->modify('+7 days'));
                        $recipient->setGroomerProfile($gp);
                        $this->em->persist($recipient);
                    }
                }

                // Add a sample suppression entry idempotently
                $suppressEmail = 'bounced@example.com';
                $exists = false;
                if ($this->emailSuppressionRepository !== null) {
                    $exists = $this->emailSuppressionRepository->isSuppressed($suppressEmail);
                } else {
                    $exists = null !== $this->em->getRepository(EmailSuppression::class)->findOneBy(['email' => $suppressEmail]);
                }
                if (!$exists) {
                    $suppress = new EmailSuppression($suppressEmail, 'bounce');
                    $this->em->persist($suppress);
                }
            }

            $this->em->flush();

            if ($withSamples) {
                // Create an audit log entry for the created lead via repository helper (if available)
                $leadEntity = $this->em->getRepository(Lead::class)->findOneBy(['email' => 'jane.owner@example.com']);
                if ($leadEntity instanceof Lead && $this->auditLogs !== null) {
                    $this->auditLogs->logLeadCreated($leadEntity, ['source' => 'seeder']);
                    $this->em->flush();
                } elseif ($leadEntity instanceof Lead && $leadEntity->getId() !== null) {
                    // Fallback without repository (should rarely occur)
                    $log = new AuditLog(
                        event: 'lead.created',
                        actorType: AuditLog::ACTOR_SYSTEM,
                        actorId: null,
                        subjectType: AuditLog::SUBJECT_LEAD,
                        subjectId: $leadEntity->getId(),
                        metadata: ['source' => 'seeder']
                    );
                    $this->em->persist($log);
                    $this->em->flush();
                }
            }
        });
    }

    private function slugify(string $source): string
    {
        $normalized = preg_replace('/\s+/', ' ', mb_strtolower(trim($source))) ?? '';

        return (new AsciiSlugger())->slug($normalized)->lower()->toString();
    }
}
