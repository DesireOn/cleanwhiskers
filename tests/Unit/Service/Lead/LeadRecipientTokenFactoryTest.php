<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Service\Lead\LeadRecipientTokenFactory;
use PHPUnit\Framework\TestCase;

final class LeadRecipientTokenFactoryTest extends TestCase
{
    public function testIssueReturnsHashRawAndExpiresAt(): void
    {
        $factory = new LeadRecipientTokenFactory();
        [$hash, $raw, $expiresAt] = $factory->issue(5);

        self::assertIsString($hash);
        self::assertIsString($raw);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash, 'Hash should be sha256 hex');
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $raw, 'Raw should be 16 random bytes hex');
        self::assertInstanceOf(\DateTimeImmutable::class, $expiresAt);

        $now = new \DateTimeImmutable('+3 minutes');
        $later = new \DateTimeImmutable('+10 minutes');
        self::assertGreaterThan($now, $expiresAt);
        self::assertLessThan($later, $expiresAt);
    }

    public function testIssueEnforcesMinimumOneMinuteTtl(): void
    {
        $factory = new LeadRecipientTokenFactory();
        [, , $expiresAt] = $factory->issue(0);

        $min = new \DateTimeImmutable('+30 seconds');
        $max = new \DateTimeImmutable('+3 minutes');
        self::assertGreaterThan($min, $expiresAt);
        self::assertLessThan($max, $expiresAt);
    }
}

