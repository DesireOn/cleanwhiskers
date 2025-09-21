<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\Service;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class LeadEntityTest extends TestCase
{
    private function makeCity(): City
    {
        return new City('Test City');
    }

    private function makeService(): Service
    {
        return (new Service())->setName('Mobile Dog Grooming');
    }

    public function testConstructorDefaultsAndAccessors(): void
    {
        $city = $this->makeCity();
        $service = $this->makeService();
        $lead = new Lead($city, $service, 'Owner Name', 'OWNER@Example.com');

        self::assertNull($lead->getId());
        self::assertSame($city, $lead->getCity());
        self::assertSame($service, $lead->getService());
        self::assertSame('Owner Name', $lead->getFullName());
        self::assertSame('OWNER@Example.com', $lead->getEmail());
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

        // Accessors
        $lead->setFullName('New Name');
        $lead->setEmail('new@example.com');
        $lead->setPhone('555');
        $lead->setPetType('Dog');
        $lead->setBreedSize('Small');
        $lead->setConsentToShare(true);
        $lead->setStatus(Lead::STATUS_CLAIMED);

        $user = (new User())->setEmail('g@example.com')->setRoles([User::ROLE_GROOMER])->setPassword('hash');
        $groomer = new GroomerProfile($user, $city, 'Pro', 'About');
        $lead->setClaimedBy($groomer);
        $claimedAt = new \DateTimeImmutable('+1 hour');
        $lead->setClaimedAt($claimedAt);
        $lead->setOwnerTokenHash('hash123');
        $expires = new \DateTimeImmutable('+2 days');
        $lead->setOwnerTokenExpiresAt($expires);
        $updated = new \DateTimeImmutable('+3 hours');
        $lead->setUpdatedAt($updated);
        $lead->setSubmissionFingerprint('fp');

        self::assertSame('New Name', $lead->getFullName());
        self::assertSame('new@example.com', $lead->getEmail());
        self::assertSame('555', $lead->getPhone());
        self::assertSame('Dog', $lead->getPetType());
        self::assertSame('Small', $lead->getBreedSize());
        self::assertTrue($lead->hasConsentToShare());
        self::assertSame(Lead::STATUS_CLAIMED, $lead->getStatus());
        self::assertSame($groomer, $lead->getClaimedBy());
        self::assertSame($claimedAt, $lead->getClaimedAt());
        self::assertSame('hash123', $lead->getOwnerTokenHash());
        self::assertSame($expires, $lead->getOwnerTokenExpiresAt());
        self::assertSame($updated, $lead->getUpdatedAt());
        self::assertSame('fp', $lead->getSubmissionFingerprint());

        // Reset nullable fields
        $lead->setBreedSize(null);
        $lead->setClaimedBy(null);
        $lead->setClaimedAt(null);
        $lead->setOwnerTokenExpiresAt(null);
        $lead->setSubmissionFingerprint(null);

        self::assertNull($lead->getBreedSize());
        self::assertNull($lead->getClaimedBy());
        self::assertNull($lead->getClaimedAt());
        self::assertNull($lead->getOwnerTokenExpiresAt());
        self::assertNull($lead->getSubmissionFingerprint());
    }
}

