<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Repository\GroomerProfileRepository;

final class LeadSegmentationService
{
    public function __construct(private readonly GroomerProfileRepository $profiles)
    {
    }

    /**
     * Basic v1 segmentation by city + service.
     * Optionally requires phone and matches specialties with pet-type.
     *
     * @return array<int, GroomerProfile>
     */
    public function segment(
        Lead $lead,
        int $limit = 20,
        bool $mustHavePhone = true,
        bool $matchSpecialtyToPetType = false,
    ): array {
        $groomers = $this->profiles->findByCityAndService($lead->getCity(), $lead->getService(), $limit, 0);

        $petType = mb_strtolower($lead->getPetType());

        $groomers = array_filter($groomers, static function (GroomerProfile $g) use ($mustHavePhone, $matchSpecialtyToPetType, $petType): bool {
            if ($mustHavePhone && null === $g->getPhone()) {
                return false;
            }

            if ($matchSpecialtyToPetType) {
                $specialties = array_map(static fn ($s) => is_string($s) ? mb_strtolower($s) : $s, $g->getSpecialties());
                foreach ($specialties as $s) {
                    if (is_string($s) && str_contains($s, $petType)) {
                        return true;
                    }
                }
                // No specialty matched
                return false;
            }

            return true;
        });

        // Maintain original ordering, but ensure it's an array with numeric keys
        return array_values($groomers);
    }
}

