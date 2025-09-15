<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service as GroomingService;
use App\Entity\User;
use App\Service\Lead\LeadClaimResult;
use App\Service\Lead\LeadClaimService;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LeadClaimServiceTest extends TestCase
{
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;
    /** @var Connection&MockObject */
    private Connection $conn;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->conn = $this->createMock(Connection::class);
        $this->em->method('getConnection')->willReturn($this->conn);
        // Run transactional closure directly
        $this->conn->method('transactional')->willReturnCallback(function (callable $func) {
            return $func($this->conn);
        });
    }

    public function testInvalidWhenIdsMissing(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        // No IDs set
        $service = new LeadClaimService($this->em, $this->createMock(\App\Repository\LeadRepository::class), $this->createMock(\App\Repository\LeadRecipientRepository::class));
        $res = $service->claim($lead, $recipient, null);
        self::assertFalse($res->isSuccess());
        self::assertSame('invalid_recipient', $res->getCode());
    }

    public function testInvalidWhenLeadNotFound(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        $this->em->method('find')->willReturnMap([
            [Lead::class, 10, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE, null], // lead not found
        ]);

        $service = new LeadClaimService($this->em, $this->createMock(\App\Repository\LeadRepository::class), $this->createMock(\App\Repository\LeadRecipientRepository::class));
        $res = $service->claim($lead, $recipient, null);
        self::assertSame('invalid_recipient', $res->getCode());
    }

    public function testAlreadyClaimedWhenLeadStatusNotPending(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);
        $lead->setStatus(Lead::STATUS_CLAIMED);

        // Locked lead returned with non-pending status
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead) {
            return $cls === Lead::class ? $lead : null;
        });

        $service = new LeadClaimService($this->em, $this->createMock(\App\Repository\LeadRepository::class), $this->createMock(\App\Repository\LeadRecipientRepository::class));
        $res = $service->claim($lead, $recipient, null);
        self::assertSame('already_claimed', $res->getCode());
    }

    public function testInvalidWhenRecipientNotFoundOrMismatch(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead) {
            if ($cls === Lead::class) {
                return $lead; // found lead OK
            }
            return null; // recipient not found
        });

        $service = new LeadClaimService($this->em, $this->createMock(\App\Repository\LeadRepository::class), $this->createMock(\App\Repository\LeadRecipientRepository::class));
        $res = $service->claim($lead, $recipient, null);
        self::assertSame('invalid_recipient', $res->getCode());

        // Now return a recipient that belongs to a different lead
        [$otherLead] = $this->makeLeadAndRecipient();
        $this->setId($otherLead, 99);
        $managedRecipient = new LeadRecipient($otherLead, 'x@example.com', hash('sha256', 'tok'), new DateTimeImmutable('+1h'));
        $this->setId($managedRecipient, 5);
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $managedRecipient) {
            return $cls === Lead::class ? $lead : $managedRecipient;
        });

        $res2 = $service->claim($lead, $recipient, null);
        self::assertSame('invalid_recipient', $res2->getCode());
    }

    public function testSuccessfulGuestClaim(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        // Em find returns locked lead then recipient belonging to same lead
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            if ($cls === Lead::class) {
                return $lead;
            }
            return $recipient;
        });

        // Expect a flush
        $this->em->expects(self::once())->method('flush');

        $service = new LeadClaimService($this->em, $this->createMock(\App\Repository\LeadRepository::class), $this->createMock(\App\Repository\LeadRecipientRepository::class));
        $res = $service->claim($lead, $recipient, null); // guest

        self::assertTrue($res->isSuccess());
        $lockedLead = $res->getLead();
        self::assertInstanceOf(Lead::class, $lockedLead);
        self::assertSame(Lead::STATUS_CLAIMED, $lockedLead->getStatus());
        self::assertNull($lockedLead->getClaimedBy());
        self::assertNotNull($lockedLead->getClaimedAt());
        self::assertNotNull($recipient->getClaimedAt());
    }

    public function testSuccessfulAuthenticatedGroomerClaim(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        $city = new City('Sofia');
        $user = (new User())->setEmail('pro@example.com')->setPassword('x')->setRoles([User::ROLE_GROOMER]);
        $groomer = new GroomerProfile($user, $city, 'Biz', 'About');

        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });
        $this->em->expects(self::once())->method('flush');

        $service = new LeadClaimService($this->em, $this->createMock(\App\Repository\LeadRepository::class), $this->createMock(\App\Repository\LeadRecipientRepository::class));
        $res = $service->claim($lead, $recipient, $groomer);

        self::assertTrue($res->isSuccess());
        $managedLead = $res->getLead();
        self::assertSame($groomer->getId(), $managedLead?->getClaimedBy()?->getId());
        self::assertNotNull($recipient->getClaimedAt());
    }

    public function testClearsReleaseAndSetsUndoWindowWhenEnabled(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        // Pre-set previous release markers that should be cleared
        $recipient->setReleasedAt(new DateTimeImmutable('-5 minutes'));
        $recipient->setReleaseAllowedUntil(new DateTimeImmutable('+30 minutes'));

        // EntityManager returns managed lead and recipient
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });
        $this->em->expects(self::once())->method('flush');

        // Enable undo with a custom window
        $service = new LeadClaimService(
            $this->em,
            $this->createMock(\App\Repository\LeadRepository::class),
            $this->createMock(\App\Repository\LeadRecipientRepository::class),
            enableUndo: true,
            undoWindowMinutes: 15,
        );

        $res = $service->claim($lead, $recipient, null);
        self::assertTrue($res->isSuccess());

        // releasedAt cleared, new releaseAllowedUntil set relative to claimedAt
        self::assertNull($recipient->getReleasedAt());
        self::assertInstanceOf(DateTimeImmutable::class, $recipient->getReleaseAllowedUntil());
        self::assertInstanceOf(DateTimeImmutable::class, $recipient->getClaimedAt());
        $delta = $recipient->getReleaseAllowedUntil()->getTimestamp() - $recipient->getClaimedAt()->getTimestamp();
        self::assertSame(15 * 60, $delta, 'Undo window should be exactly 15 minutes after claimedAt');
    }

    public function testNoUndoFieldsWhenDisabled(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        // Pre-set to verify they get cleared appropriately
        $recipient->setReleasedAt(new DateTimeImmutable('-10 minutes'));
        $recipient->setReleaseAllowedUntil(new DateTimeImmutable('+10 minutes'));

        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });
        $this->em->expects(self::once())->method('flush');

        // Disable undo
        $service = new LeadClaimService(
            $this->em,
            $this->createMock(\App\Repository\LeadRepository::class),
            $this->createMock(\App\Repository\LeadRecipientRepository::class),
            enableUndo: false,
            undoWindowMinutes: 10,
        );

        $res = $service->claim($lead, $recipient, null);
        self::assertTrue($res->isSuccess());
        self::assertNull($recipient->getReleasedAt());
        self::assertNull($recipient->getReleaseAllowedUntil());
    }

    public function testAlreadyClaimedWhenClaimedByExists(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        // Lead appears pending but has claimedBy set
        $user = (new User())->setEmail('pro@example.com')->setPassword('x')->setRoles([User::ROLE_GROOMER]);
        $groomer = new GroomerProfile($user, new City('Sofia'), 'Biz', 'About');
        $lead->setClaimedBy($groomer);

        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead) {
            return $cls === Lead::class ? $lead : null;
        });

        $service = new LeadClaimService(
            $this->em,
            $this->createMock(\App\Repository\LeadRepository::class),
            $this->createMock(\App\Repository\LeadRecipientRepository::class)
        );
        $res = $service->claim($lead, $recipient, null);
        self::assertSame('already_claimed', $res->getCode());
    }

    public function testUndoWindowMinimumOneMinute(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });

        $service = new LeadClaimService(
            $this->em,
            $this->createMock(\App\Repository\LeadRepository::class),
            $this->createMock(\App\Repository\LeadRecipientRepository::class),
            enableUndo: true,
            undoWindowMinutes: 0, // should clamp to 1 minute
        );
        $res = $service->claim($lead, $recipient, null);

        self::assertTrue($res->isSuccess());
        self::assertInstanceOf(DateTimeImmutable::class, $recipient->getReleaseAllowedUntil());
        $delta = $recipient->getReleaseAllowedUntil()->getTimestamp() - $recipient->getClaimedAt()->getTimestamp();
        self::assertSame(60, $delta, 'Undo window should be minimum 1 minute');
    }

    // No need for repository behavior in these unit tests; we provide mocks above.

    /**
     * @return array{0: Lead, 1: LeadRecipient}
     */
    private function makeLeadAndRecipient(): array
    {
        $city = new City('Sofia');
        $service = (new GroomingService())->setName('Mobile Dog Grooming');
        $lead = new Lead($city, $service, 'Owner', 'owner@example.com');
        $recipient = new LeadRecipient($lead, 'invitee@example.com', hash('sha256', 'tok'), new DateTimeImmutable('+1 hour'));
        return [$lead, $recipient];
    }

    /**
     * @param object $entity
     */
    private function setId(object $entity, int $id): void
    {
        $ref = new \ReflectionClass($entity);
        while (!$ref->hasProperty('id') && $ref = $ref->getParentClass()) {
            // walk up the chain
        }
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($entity, $id);
    }
}
