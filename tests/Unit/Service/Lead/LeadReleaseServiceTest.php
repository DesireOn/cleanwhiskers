<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\AuditLog;
use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service as GroomingService;
use App\Entity\User;
use App\Repository\AuditLogRepository;
use App\Service\Lead\LeadReleaseResult;
use App\Service\Lead\LeadReleaseService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LeadReleaseServiceTest extends TestCase
{
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;
    /** @var Connection&MockObject */
    private Connection $conn;
    /** @var AuditLogRepository&MockObject */
    private AuditLogRepository $auditLogs;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->conn = $this->createMock(Connection::class);
        $this->auditLogs = $this->createMock(AuditLogRepository::class);

        $this->em->method('getConnection')->willReturn($this->conn);
        // Execute transactional closure immediately
        $this->conn->method('transactional')->willReturnCallback(function (callable $func) {
            return $func($this->conn);
        });
    }

    public function testInvalidWhenIdsMissing(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        // Ensure IDs are null
        $service = new LeadReleaseService($this->em, $this->auditLogs);
        $res = $service->release($lead, $recipient);
        self::assertFalse($res->isSuccess());
        self::assertSame('invalid_recipient', $res->getCode());
    }

    public function testInvalidWhenLeadNotFound(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        // Lead not found under lock
        $this->em->method('find')->willReturnMap([
            [Lead::class, 10, LockMode::PESSIMISTIC_WRITE, null, null],
        ]);

        $service = new LeadReleaseService($this->em, $this->auditLogs);
        $res = $service->release($lead, $recipient);
        self::assertSame('invalid_recipient', $res->getCode());
    }

    public function testNotClaimedWhenLeadStatusIsNotClaimed(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);
        $lead->setStatus(Lead::STATUS_PENDING);

        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead) {
            return $cls === Lead::class ? $lead : null;
        });

        $service = new LeadReleaseService($this->em, $this->auditLogs);
        $res = $service->release($lead, $recipient);
        self::assertSame('not_claimed', $res->getCode());
    }

    public function testInvalidWhenRecipientNotFoundOrMismatch(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);
        $lead->setStatus(Lead::STATUS_CLAIMED);

        // Recipient not found
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead) {
            return $cls === Lead::class ? $lead : null;
        });

        $service = new LeadReleaseService($this->em, $this->auditLogs);
        $res = $service->release($lead, $recipient);
        self::assertSame('invalid_recipient', $res->getCode());

        // Recipient belongs to a different lead
        [$otherLead] = $this->makeLeadAndRecipient();
        $this->setId($otherLead, 99);
        $managedRecipient = new LeadRecipient($otherLead, 'x@example.com', hash('sha256', 'tok'), new DateTimeImmutable('+1h'));
        $this->setId($managedRecipient, 5);

        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $managedRecipient) {
            return $cls === Lead::class ? $lead : $managedRecipient;
        });

        $res2 = $service->release($lead, $recipient);
        self::assertSame('invalid_recipient', $res2->getCode());
    }

    public function testAlreadyReleasedWhenRecipientHasReleasedAt(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);
        $lead->setStatus(Lead::STATUS_CLAIMED);
        $recipient->setReleasedAt(new DateTimeImmutable('-1 minute'));

        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });

        $service = new LeadReleaseService($this->em, $this->auditLogs);
        $res = $service->release($lead, $recipient);
        self::assertSame('already_released', $res->getCode());
    }

    public function testWindowExpiredWhenNullOrPast(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);
        $lead->setStatus(Lead::STATUS_CLAIMED);

        // Case 1: null allowed-until
        $recipient->setReleaseAllowedUntil(null);
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });
        $service = new LeadReleaseService($this->em, $this->auditLogs);
        $res1 = $service->release($lead, $recipient);
        self::assertSame('window_expired', $res1->getCode());

        // Case 2: in the past
        $recipient->setReleaseAllowedUntil(new DateTimeImmutable('-5 minutes'));
        $res2 = $service->release($lead, $recipient);
        self::assertSame('window_expired', $res2->getCode());
    }

    public function testSuccessfulReleaseResetsStateAndAudits(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        // Pretend the lead is currently claimed
        $lead->setStatus(Lead::STATUS_CLAIMED);

        $city = new City('Sofia');
        $user = (new User())->setEmail('pro@example.com')->setPassword('x')->setRoles([User::ROLE_GROOMER]);
        $groomer = new GroomerProfile($user, $city, 'Biz', 'About');
        // mark lead claimed by groomer
        $lead->setClaimedBy($groomer);
        $lead->setClaimedAt(new DateTimeImmutable('-10 minutes'));
        $beforeUpdate = $lead->getUpdatedAt()->sub(new DateInterval('PT30M'));
        $lead->setUpdatedAt($beforeUpdate);

        // Recipient is within allowed window and has data to be cleared
        $recipient->setReleaseAllowedUntil(new DateTimeImmutable('+10 minutes'));
        $recipient->setClaimedAt(new DateTimeImmutable('-9 minutes'));
        $recipient->setGroomerProfile($groomer);
        $recipient->setReleasedAt(null);

        // EM lookups
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });

        // Expect flush once
        $this->em->expects(self::once())->method('flush');

        // Expect audit log called with specific parameters
        $this->auditLogs->expects(self::once())->method('log')->with(
            self::equalTo('release_success'),
            self::equalTo(AuditLog::SUBJECT_LEAD),
            self::equalTo(10),
            self::callback(function ($metadata) {
                return is_array($metadata)
                    && ($metadata['recipientId'] ?? null) === 5
                    && ($metadata['recipientEmail'] ?? null) === 'invitee@example.com';
            }),
            self::equalTo(AuditLog::ACTOR_GROOMER),
            self::isNull(),
        );

        $service = new LeadReleaseService($this->em, $this->auditLogs);
        $res = $service->release($lead, $recipient);

        self::assertTrue($res->isSuccess());
        self::assertSame('success', $res->getCode());
        // Lead reset
        self::assertSame(Lead::STATUS_PENDING, $lead->getStatus());
        self::assertNull($lead->getClaimedBy());
        self::assertNull($lead->getClaimedAt());
        self::assertGreaterThan($beforeUpdate->getTimestamp(), $lead->getUpdatedAt()->getTimestamp());
        // Recipient reset
        self::assertNull($recipient->getClaimedAt());
        self::assertNull($recipient->getReleasedAt());
        self::assertNull($recipient->getGroomerProfile());
    }

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
            // walk up
        }
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($entity, $id);
    }
}

