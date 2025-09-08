<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\LeadRecipient;

final class LeadClaimVerifier
{
    public function isValid(LeadRecipient $recipient, string $rawToken, ?\DateTimeImmutable $now = null): bool
    {
        $now = $now ?? new \DateTimeImmutable();
        if ($recipient->getTokenExpiresAt() <= $now) {
            return false;
        }
        $computed = hash('sha256', $rawToken);
        return hash_equals($recipient->getClaimTokenHash(), $computed);
    }
}

