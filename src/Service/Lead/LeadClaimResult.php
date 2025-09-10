<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;

final class LeadClaimResult
{
    private function __construct(
        private readonly bool $success,
        private readonly string $code,
        private readonly ?Lead $lead = null,
    ) {
    }

    public static function success(Lead $lead): self
    {
        return new self(true, 'success', $lead);
    }

    public static function alreadyClaimed(): self
    {
        return new self(false, 'already_claimed');
    }

    public static function invalidRecipient(): self
    {
        return new self(false, 'invalid_recipient');
    }

    public function isSuccess(): bool { return $this->success; }
    public function getCode(): string { return $this->code; }
    public function getLead(): ?Lead { return $this->lead; }
}

