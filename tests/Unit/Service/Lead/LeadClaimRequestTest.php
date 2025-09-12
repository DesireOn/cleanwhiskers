<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Service\Lead\LeadClaimRequest;
use PHPUnit\Framework\TestCase;

final class LeadClaimRequestTest extends TestCase
{
    public function testFromRawWithExplicitNow(): void
    {
        $now = 1700000000;
        $req = LeadClaimRequest::fromRaw(
            uri: 'https://app.test/leads/claim?lid=1&rid=2',
            lid: '1',
            rid: '2',
            email: 'pro@example.com',
            token: 'rawtok',
            exp: (string) ($now + 3600),
            nowTs: $now,
        );

        self::assertSame('https://app.test/leads/claim?lid=1&rid=2', $req->uri);
        self::assertSame('1', $req->lid);
        self::assertSame('2', $req->rid);
        self::assertSame('pro@example.com', $req->email);
        self::assertSame('rawtok', $req->token);
        self::assertSame((string) ($now + 3600), $req->exp);
        self::assertSame($now, $req->nowTs);
    }
}

