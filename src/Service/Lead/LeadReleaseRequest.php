<?php

declare(strict_types=1);

namespace App\Service\Lead;

final class LeadReleaseRequest
{
    public function __construct(
        public readonly string $uri,
        public readonly string $lid,
        public readonly string $rid,
        public readonly string $exp,
        public readonly int $nowTs,
    ) {}

    public static function fromRaw(
        string $uri,
        string $lid,
        string $rid,
        string $exp,
        ?int $nowTs = null,
    ): self {
        return new self(
            uri: $uri,
            lid: $lid,
            rid: $rid,
            exp: $exp,
            nowTs: $nowTs ?? time(),
        );
    }
}

