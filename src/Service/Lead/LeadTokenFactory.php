<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;

final class LeadTokenFactory
{
    /**
     * Issues a new owner token for the given lead, sets hash + expiry on entity, and returns the raw token.
     */
    public function issueOwnerToken(Lead $lead, ?\DateInterval $ttl = null): string
    {
        $ttl = $ttl ?? new \DateInterval('P7D');

        $raw = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);

        $lead->setOwnerTokenHash($hash);
        $lead->setOwnerTokenExpiresAt((new \DateTimeImmutable())->add($ttl));

        return $raw;
    }
}

