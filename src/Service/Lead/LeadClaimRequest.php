<?php

declare(strict_types=1);

namespace App\Service\Lead;

/**
 * Immutable DTO representing a claim request extracted from the HTTP request.
 */
final class LeadClaimRequest
{
    public function __construct(
        public readonly string $uri,
        public readonly string $lid,
        public readonly string $rid,
        public readonly string $email,
        public readonly string $token,
        public readonly string $exp,
        public readonly int $nowTs,
    ) {
    }

    public static function fromRaw(
        string $uri,
        string $lid,
        string $rid,
        string $email,
        string $token,
        string $exp,
        ?int $nowTs = null,
    ): self {
        return new self(
            uri: $uri,
            lid: $lid,
            rid: $rid,
            email: $email,
            token: $token,
            exp: $exp,
            nowTs: $nowTs ?? time(),
        );
    }
}

