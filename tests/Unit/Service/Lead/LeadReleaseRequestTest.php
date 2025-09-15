<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Service\Lead\LeadReleaseRequest;
use PHPUnit\Framework\TestCase;

final class LeadReleaseRequestTest extends TestCase
{
    public function testFromRawWithExplicitNow(): void
    {
        $now = 1_700_000_000;
        $req = LeadReleaseRequest::fromRaw(
            uri: 'https://app.test/leads/release?lid=10&rid=20',
            lid: '10',
            rid: '20',
            exp: (string) ($now + 3600),
            nowTs: $now,
        );

        self::assertSame('https://app.test/leads/release?lid=10&rid=20', $req->uri);
        self::assertSame('10', $req->lid);
        self::assertSame('20', $req->rid);
        self::assertSame((string) ($now + 3600), $req->exp);
        self::assertSame($now, $req->nowTs);
    }

    public function testFromRawDefaultsNowWhenMissing(): void
    {
        $before = time();
        $req = LeadReleaseRequest::fromRaw(
            uri: 'https://app.test/leads/release?lid=1&rid=2',
            lid: '1',
            rid: '2',
            exp: (string) ($before + 600),
        );
        $after = time();

        self::assertSame('https://app.test/leads/release?lid=1&rid=2', $req->uri);
        self::assertSame('1', $req->lid);
        self::assertSame('2', $req->rid);
        self::assertSame((string) ($before + 600), $req->exp);
        self::assertGreaterThanOrEqual($before, $req->nowTs);
        self::assertLessThanOrEqual($after, $req->nowTs);
    }
}

