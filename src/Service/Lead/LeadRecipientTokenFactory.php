<?php

declare(strict_types=1);

namespace App\Service\Lead;

final class LeadRecipientTokenFactory
{
    /**
     * Issues a token for LeadRecipient invites.
     * Returns [hash, raw, expiresAt].
     * Ensures a minimum TTL of 1 minute.
     *
     * @return array{0:string,1:string,2:\DateTimeImmutable}
     */
    public function issue(int $ttlMinutes): array
    {
        $ttl = max(1, $ttlMinutes);
        $raw = bin2hex(random_bytes(16));
        $hash = hash('sha256', $raw);
        $expiresAt = (new \DateTimeImmutable())->modify('+' . $ttl . ' minutes');

        return [$hash, $raw, $expiresAt];
    }
}

