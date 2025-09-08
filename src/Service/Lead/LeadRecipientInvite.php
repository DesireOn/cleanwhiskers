<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\LeadRecipient;

/**
 * Lightweight value object representing a pending invite to a lead recipient.
 */
final class LeadRecipientInvite
{
    public function __construct(
        public readonly LeadRecipient $recipient,
        public readonly string $rawToken,
    ) {}
}

