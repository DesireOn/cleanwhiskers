<?php

declare(strict_types=1);

namespace App\Service\Lead;

final class LeadClaimAllowance
{
    public function __construct(
        private readonly bool $allowed,
        private readonly ?string $reason = null,
        private readonly ?\DateTimeImmutable $nextAllowedAt = null,
        private readonly ?int $remainingMinutes = null,
    ) {
    }

    public static function allow(): self
    {
        return new self(true);
    }

    public static function denyCooldown(\DateTimeImmutable $nextAllowedAt, int $remainingMinutes): self
    {
        return new self(false, 'cooldown', $nextAllowedAt, $remainingMinutes);
    }

    public function isAllowed(): bool { return $this->allowed; }
    public function getReason(): ?string { return $this->reason; }
    public function getNextAllowedAt(): ?\DateTimeImmutable { return $this->nextAllowedAt; }
    public function getRemainingMinutes(): ?int { return $this->remainingMinutes; }
}

