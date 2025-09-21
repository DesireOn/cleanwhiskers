<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\Service;
use PHPUnit\Framework\TestCase;

final class LeadEntityTest extends TestCase
{
    private function makeLead(): Lead
    {
        $city = new City('Test City');
        $service = (new Service())->setName('Mobile Dog Grooming');
        return new Lead($city, $service, 'Owner Name', 'owner@example.com');
    }

    public function testConstructorSetsDefaults(): void
    {
        $lead = $this->makeLead();

        self::assertNull($lead->getId());
        self::assertSame('Owner Name', $lead->getFullName());
        self::assertSame('owner@example.com', $lead->getEmail());
        self::assertNull($lead->getPhone());
        self::assertNull($lead->getPetType());
        self::assertNull($lead->getBreedSize());
        self::assertFalse($lead->hasConsentToShare());
        self::assertSame(Lead::STATUS_PENDING, $lead->getStatus());
        self::assertNull($lead->getClaimedBy());
        self::assertNull($lead->getClaimedAt());
        self::assertSame('', $lead->getOwnerTokenHash());
        self::assertNull($lead->getOwnerTokenExpiresAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $lead->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $lead->getUpdatedAt());
        self::assertNull($lead->getSubmissionFingerprint());
    }

    public function testAccessorsAndMutators(): void
    {
        $lead = $this->makeLead();

        $lead->setFullName('New Name');
        $lead->setEmail('new@example.com');
        $lead->setPhone('555-123');
        $lead->setPetType('Dog');
        $lead->setBreedSize('Small');
        $lead->setConsentToShare(true);
        $lead->setStatus(Lead::STATUS_CLAIMED);
        $lead->setSubmissionFingerprint('abc123');

        $claimer = new GroomerProfile(null, $lead->getCity(), 'Biz', 'About');
        $lead->setClaimedBy($claimer);
        $claimedAt = new \DateTimeImmutable('+1 hour');
        $lead->setClaimedAt($claimedAt);

        $lead->setOwnerTokenHash('hash');
        $expires = new \DateTimeImmutable('+1 day');
        $lead->setOwnerTokenExpiresAt($expires);

        $now = new \DateTimeImmutable();
        $lead->setUpdatedAt($now);

        self::assertSame('New Name', $lead->getFullName());
        self::assertSame('new@example.com', $lead->getEmail());
        self::assertSame('555-123', $lead->getPhone());
        self::assertSame('Dog', $lead->getPetType());
        self::assertSame('Small', $lead->getBreedSize());
        self::assertTrue($lead->hasConsentToShare());
        self::assertSame(Lead::STATUS_CLAIMED, $lead->getStatus());
        self::assertSame('abc123', $lead->getSubmissionFingerprint());
        self::assertSame($claimer, $lead->getClaimedBy());
        self::assertSame($claimedAt, $lead->getClaimedAt());
        self::assertSame('hash', $lead->getOwnerTokenHash());
        self::assertSame($expires, $lead->getOwnerTokenExpiresAt());
        self::assertSame($now, $lead->getUpdatedAt());
    }
}

