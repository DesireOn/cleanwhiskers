<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Entity\LeadRecipient;

final class LeadClaimProcessorResult
{
    private function __construct(
        private readonly bool $success,
        private readonly string $code,
        private readonly Lead $lead,
        private readonly LeadRecipient $recipient,
    ) {}

    public static function success(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(true, 'success', $lead, $recipient);
    }

    public static function alreadyClaimedByYou(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(true, 'already_claimed_by_you', $lead, $recipient);
    }

    public static function alreadyClaimed(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(false, 'already_claimed', $lead, $recipient);
    }

    public static function invalidRecipient(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(false, 'invalid_recipient', $lead, $recipient);
    }

    public function isSuccess(): bool { return $this->success; }
    public function getCode(): string { return $this->code; }
    public function getLead(): Lead { return $this->lead; }
    public function getRecipient(): LeadRecipient { return $this->recipient; }
}

