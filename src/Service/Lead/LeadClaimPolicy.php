<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\GroomerProfile;
use App\Repository\LeadRepository;

final class LeadClaimPolicy
{
    private const DEFAULT_CLAIM_COOLDOWN_HOURS = 2;

    public function __construct(
        private readonly LeadRepository $leads,
        private readonly int $cooldownHours = self::DEFAULT_CLAIM_COOLDOWN_HOURS,
    ) {
    }

    public function canClaim(GroomerProfile $groomer): LeadClaimAllowance
    {
        $last = $this->leads->findLastClaimedByGroomer($groomer);
        if ($last === null || $last->getClaimedAt() === null) {
            return LeadClaimAllowance::allow();
        }

        $claimedAt = $last->getClaimedAt();
        $nextAllowedAt = $claimedAt->modify(sprintf('+%d hours', $this->cooldownHours));
        $now = new \DateTimeImmutable();

        if ($now < $nextAllowedAt) {
            $remainingMinutes = (int) max(0, ($nextAllowedAt->getTimestamp() - $now->getTimestamp()) / 60);
            return LeadClaimAllowance::denyCooldown($nextAllowedAt, $remainingMinutes);
        }

        return LeadClaimAllowance::allow();
    }
}

