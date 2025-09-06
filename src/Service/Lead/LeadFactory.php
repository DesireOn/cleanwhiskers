<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service;

final class LeadFactory
{
    /**
     * @param array{petType:string, ownerName:string, ownerPhone:string, ownerEmail?:string|null, breedSize?:string|null, claimFeeCents?:int} $data
     */
    public function create(City $city, Service $service, array $data): Lead
    {
        $token = $this->generateToken();

        $lead = new Lead(
            $city,
            $service,
            $data['petType'],
            $data['ownerName'],
            $data['ownerPhone'],
            $token,
        );

        if (isset($data['ownerEmail'])) {
            $lead->setOwnerEmail($data['ownerEmail']);
        }
        if (isset($data['breedSize'])) {
            $lead->setBreedSize($data['breedSize']);
        }
        if (isset($data['claimFeeCents'])) {
            $lead->setClaimFeeCents((int) $data['claimFeeCents']);
        }

        return $lead;
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }
}

