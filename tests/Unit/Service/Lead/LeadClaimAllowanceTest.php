<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Service\Lead\LeadClaimAllowance;
use PHPUnit\Framework\TestCase;

final class LeadClaimAllowanceTest extends TestCase
{
    public function testAllowFactory(): void
    {
        $allow = LeadClaimAllowance::allow();
        self::assertTrue($allow->isAllowed());
        self::assertNull($allow->getReason());
        self::assertNull($allow->getNextAllowedAt());
        self::assertNull($allow->getRemainingMinutes());
    }

    public function testDenyCooldownFactory(): void
    {
        $future = new \DateTimeImmutable('+90 minutes');
        $deny = LeadClaimAllowance::denyCooldown($future, 90);

        self::assertFalse($deny->isAllowed());
        self::assertSame('cooldown', $deny->getReason());
        self::assertSame($future->getTimestamp(), $deny->getNextAllowedAt()?->getTimestamp());
        self::assertSame(90, $deny->getRemainingMinutes());
    }
}

