<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service as GroomingService;
use App\Service\Lead\LeadClaimResult;
use PHPUnit\Framework\TestCase;

final class LeadClaimResultTest extends TestCase
{
    public function testSuccessFactory(): void
    {
        $city = new City('X');
        $service = (new GroomingService())->setName('Grooming');
        $lead = new Lead($city, $service, 'Owner', 'owner@example.com');

        $res = LeadClaimResult::success($lead);
        self::assertTrue($res->isSuccess());
        self::assertSame('success', $res->getCode());
        self::assertSame($lead, $res->getLead());
    }

    public function testAlreadyClaimedFactory(): void
    {
        $res = LeadClaimResult::alreadyClaimed();
        self::assertFalse($res->isSuccess());
        self::assertSame('already_claimed', $res->getCode());
        self::assertNull($res->getLead());
    }

    public function testInvalidRecipientFactory(): void
    {
        $res = LeadClaimResult::invalidRecipient();
        self::assertFalse($res->isSuccess());
        self::assertSame('invalid_recipient', $res->getCode());
        self::assertNull($res->getLead());
    }
}

