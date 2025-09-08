<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service;
use App\Service\Lead\LeadTokenFactory;
use PHPUnit\Framework\TestCase;

final class LeadTokenFactoryTest extends TestCase
{
    public function testIssueOwnerTokenSetsHashAndExpiry(): void
    {
        $lead = new Lead(new City('Sofia'), (new Service())->setName('Mobile Dog Grooming'), 'Owner', 'owner@example.com');
        $factory = new LeadTokenFactory();

        $raw = $factory->issueOwnerToken($lead);

        self::assertIsString($raw);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $raw);
        self::assertNotEmpty($lead->getOwnerTokenHash());
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $lead->getOwnerTokenHash());
        self::assertInstanceOf(\DateTimeImmutable::class, $lead->getOwnerTokenExpiresAt());

        $min = new \DateTimeImmutable('+1 day');
        $max = new \DateTimeImmutable('+10 days');
        self::assertGreaterThan($min, $lead->getOwnerTokenExpiresAt());
        self::assertLessThan($max, $lead->getOwnerTokenExpiresAt());
    }
}

