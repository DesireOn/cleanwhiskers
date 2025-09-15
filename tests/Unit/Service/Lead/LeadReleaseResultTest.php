<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Service\Lead\LeadReleaseResult;
use PHPUnit\Framework\TestCase;

final class LeadReleaseResultTest extends TestCase
{
    public function testSuccessFactory(): void
    {
        $res = LeadReleaseResult::success();
        self::assertTrue($res->isSuccess());
        self::assertSame('success', $res->getCode());
    }

    public function testNotClaimedFactory(): void
    {
        $res = LeadReleaseResult::notClaimed();
        self::assertFalse($res->isSuccess());
        self::assertSame('not_claimed', $res->getCode());
    }

    public function testInvalidRecipientFactory(): void
    {
        $res = LeadReleaseResult::invalidRecipient();
        self::assertFalse($res->isSuccess());
        self::assertSame('invalid_recipient', $res->getCode());
    }

    public function testWindowExpiredFactory(): void
    {
        $res = LeadReleaseResult::windowExpired();
        self::assertFalse($res->isSuccess());
        self::assertSame('window_expired', $res->getCode());
    }

    public function testAlreadyReleasedFactory(): void
    {
        $res = LeadReleaseResult::alreadyReleased();
        self::assertFalse($res->isSuccess());
        self::assertSame('already_released', $res->getCode());
    }
}

