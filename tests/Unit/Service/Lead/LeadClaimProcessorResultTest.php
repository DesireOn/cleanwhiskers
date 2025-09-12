<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service as GroomingService;
use App\Entity\LeadRecipient;
use App\Service\Lead\LeadClaimProcessorResult;
use PHPUnit\Framework\TestCase;

final class LeadClaimProcessorResultTest extends TestCase
{
    public function testFactories(): void
    {
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');
        $lead = new Lead($city, $service, 'Owner', 'o@example.com');
        $recipient = new LeadRecipient($lead, 'r@example.com', hash('sha256', 't'), new \DateTimeImmutable('+1 hour'));

        $ok = LeadClaimProcessorResult::success($lead, $recipient);
        self::assertTrue($ok->isSuccess());
        self::assertSame('success', $ok->getCode());
        self::assertSame($lead, $ok->getLead());
        self::assertSame($recipient, $ok->getRecipient());

        $byYou = LeadClaimProcessorResult::alreadyClaimedByYou($lead, $recipient);
        self::assertTrue($byYou->isSuccess());
        self::assertSame('already_claimed_by_you', $byYou->getCode());

        $conflict = LeadClaimProcessorResult::alreadyClaimed($lead, $recipient);
        self::assertFalse($conflict->isSuccess());
        self::assertSame('already_claimed', $conflict->getCode());

        $invalid = LeadClaimProcessorResult::invalidRecipient($lead, $recipient);
        self::assertFalse($invalid->isSuccess());
        self::assertSame('invalid_recipient', $invalid->getCode());
    }
}

