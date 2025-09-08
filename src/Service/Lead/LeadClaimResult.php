<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Entity\LeadRecipient;

final class LeadClaimResult
{
    /**
     * @param array<string,mixed> $context
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $code,
        public readonly string $message,
        public readonly ?Lead $lead = null,
        public readonly ?LeadRecipient $recipient = null,
        public readonly array $context = [],
    ) {}

    public static function ok(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(true, 'ok', 'Lead successfully claimed.', $lead, $recipient);
    }

    /**
     * @param array<string,mixed> $context
     */
    public static function fail(string $code, string $message, array $context = []): self
    {
        return new self(false, $code, $message, null, null, $context);
    }
}

