<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Entity\LeadRecipient;
use Symfony\Component\HttpFoundation\Response;

final class LeadClaimValidationResult
{
    public const OK = 'ok';
    public const INVALID_SIGNATURE = 'invalid_signature';
    public const EXPIRED = 'expired';
    public const MISSING_PARAMS = 'missing_params';
    public const NOT_FOUND = 'not_found';
    public const INVALID_TOKEN_OR_EMAIL = 'invalid_token_or_email';
    public const RECIPIENT_EXPIRED = 'recipient_expired';
    /**
     * Special case: Even if link invalid/expired, allow re-show success page when
     * the same recipient already claimed this lead earlier and token/email match.
     */
    public const ALLOW_GUEST_RESHOW = 'allow_guest_reshow';

    private function __construct(
        private readonly string $code,
        private readonly ?Lead $lead = null,
        private readonly ?LeadRecipient $recipient = null,
    ) {}

    public static function ok(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(self::OK, $lead, $recipient);
    }

    public static function invalidSignature(?Lead $lead = null, ?LeadRecipient $recipient = null): self
    {
        return new self(self::INVALID_SIGNATURE, $lead, $recipient);
    }

    public static function expired(?Lead $lead = null, ?LeadRecipient $recipient = null): self
    {
        return new self(self::EXPIRED, $lead, $recipient);
    }

    public static function missingParams(): self
    {
        return new self(self::MISSING_PARAMS);
    }

    public static function notFound(): self
    {
        return new self(self::NOT_FOUND);
    }

    public static function invalidTokenOrEmail(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(self::INVALID_TOKEN_OR_EMAIL, $lead, $recipient);
    }

    public static function recipientExpired(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(self::RECIPIENT_EXPIRED, $lead, $recipient);
    }

    public static function allowGuestReshow(Lead $lead, LeadRecipient $recipient): self
    {
        return new self(self::ALLOW_GUEST_RESHOW, $lead, $recipient);
    }

    public function isOk(): bool { return $this->code === self::OK; }
    public function getCode(): string { return $this->code; }
    public function getLead(): ?Lead { return $this->lead; }
    public function getRecipient(): ?LeadRecipient { return $this->recipient; }

    public function httpStatus(): int
    {
        return match ($this->code) {
            self::OK, self::ALLOW_GUEST_RESHOW => Response::HTTP_OK,
            self::NOT_FOUND => Response::HTTP_NOT_FOUND,
            default => Response::HTTP_BAD_REQUEST,
        };
    }
}

