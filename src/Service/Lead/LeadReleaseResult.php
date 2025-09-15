<?php

declare(strict_types=1);

namespace App\Service\Lead;

final class LeadReleaseResult
{
    private function __construct(
        private readonly bool $success,
        private readonly string $code,
    ) {}

    public static function success(): self { return new self(true, 'success'); }
    public static function notClaimed(): self { return new self(false, 'not_claimed'); }
    public static function invalidRecipient(): self { return new self(false, 'invalid_recipient'); }
    public static function windowExpired(): self { return new self(false, 'window_expired'); }
    public static function alreadyReleased(): self { return new self(false, 'already_released'); }

    public function isSuccess(): bool { return $this->success; }
    public function getCode(): string { return $this->code; }
}

