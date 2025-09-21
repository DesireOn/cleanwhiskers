<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service;
use PHPUnit\Framework\TestCase;

final class LeadRecipientEntityTest extends TestCase
{
    private function makeLead(): Lead
    {
        $city = new City('Test City');
        $service = (new Service())->setName('Mobile Dog Grooming');
        return new Lead($city, $service, 'Owner Name', 'owner@example.com');
    }

    public function testConstructorAndDefaults(): void
    {
        $lead = $this->makeLead();
        $expires = new \DateTimeImmutable('+2 days');
        $r = new LeadRecipient($lead, 'groomer@example.com', 'hash123', $expires);

        self::assertSame($lead, $r->getLead());
        self::assertSame('groomer@example.com', $r->getEmail());
        self::assertSame('queued', $r->getStatus());
        self::assertNull($r->getInviteSentAt());
        self::assertSame($expires, $r->getTokenExpiresAt());
        self::assertNull($r->getClaimedAt());
        self::assertNull($r->getReleaseAllowedUntil());
        self::assertNull($r->getReleasedAt());
        self::assertNull($r->getCommitmentConfirmedAt());
        self::assertNull($r->getContactedAt());
        self::assertNull($r->getAutoReleasedAt());
        self::assertNull($r->getReleaseReason());
        self::assertInstanceOf(\DateTimeImmutable::class, $r->getCreatedAt());
    }

    public function testNewTimestampsAndReasonAccessors(): void
    {
        $lead = $this->makeLead();
        $r = new LeadRecipient($lead, 'groomer@example.com', 'hash', new \DateTimeImmutable('+1 day'));

        $commitment = new \DateTimeImmutable('+1 hour');
        $contacted = new \DateTimeImmutable('+2 hours');
        $autoRel = new \DateTimeImmutable('+3 hours');

        $r->setCommitmentConfirmedAt($commitment);
        $r->setContactedAt($contacted);
        $r->setAutoReleasedAt($autoRel);
        $r->setReleaseReason('no response');

        self::assertSame($commitment, $r->getCommitmentConfirmedAt());
        self::assertSame($contacted, $r->getContactedAt());
        self::assertSame($autoRel, $r->getAutoReleasedAt());
        self::assertSame('no response', $r->getReleaseReason());

        // Reset to null
        $r->setCommitmentConfirmedAt(null);
        $r->setContactedAt(null);
        $r->setAutoReleasedAt(null);
        $r->setReleaseReason(null);

        self::assertNull($r->getCommitmentConfirmedAt());
        self::assertNull($r->getContactedAt());
        self::assertNull($r->getAutoReleasedAt());
        self::assertNull($r->getReleaseReason());
    }
}

