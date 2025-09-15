<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Entity\LeadRecipient;
use Symfony\Component\HttpFoundation\Response;

final class LeadReleaseValidationResult
{
    public const OK = 'ok';
    public const INVALID_SIGNATURE = 'invalid_signature';
    public const EXPIRED = 'expired';
    public const MISSING_PARAMS = 'missing_params';
    public const NOT_FOUND = 'not_found';

    private function __construct(
        private readonly string $code,
        private readonly ?Lead $lead = null,
        private readonly ?LeadRecipient $recipient = null,
    ) {}

    public static function ok(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(self::OK, $lead, $recipient);
    }

    public static function invalidSignature(): self
    {
        return new self(self::INVALID_SIGNATURE);
    }

    public static function expired(): self
    {
        return new self(self::EXPIRED);
    }

    public static function missingParams(): self
    {
        return new self(self::MISSING_PARAMS);
    }

    public static function notFound(): self
    {
        return new self(self::NOT_FOUND);
    }

    public function isOk(): bool { return $this->code === self::OK; }
    public function getCode(): string { return $this->code; }
    public function getLead(): ?Lead { return $this->lead; }
    public function getRecipient(): ?LeadRecipient { return $this->recipient; }

    public function httpStatus(): int
    {
        return match ($this->code) {
            self::OK => Response::HTTP_OK,
            self::NOT_FOUND => Response::HTTP_NOT_FOUND,
            default => Response::HTTP_BAD_REQUEST,
        };
    }
}

