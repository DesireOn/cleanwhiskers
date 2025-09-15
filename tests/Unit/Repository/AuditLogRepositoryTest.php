<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\AuditLog;
use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AuditLogRepositoryTest extends TestCase
{
    /** @var ManagerRegistry&MockObject */
    private ManagerRegistry $registry;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
    }

    public function testLogLeadCreatedPersistsWithDefaults(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $lead = new Lead(new City('Denver'), (new Service())->setName('Mobile Dog Grooming'), 'Owner', 'owner@example.com');
        $leadRef = new \ReflectionProperty($lead, 'id');
        $leadRef->setAccessible(true);
        $leadRef->setValue($lead, 321);

        $persisted = null;
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persisted): void {
                $persisted = $entity;
            });

        $repo->logLeadCreated($lead, ['source' => 'unit']);

        self::assertInstanceOf(AuditLog::class, $persisted);
        self::assertSame('lead.created', $persisted->getEvent());
        self::assertSame(AuditLog::SUBJECT_LEAD, $persisted->getSubjectType());
        self::assertSame(321, $persisted->getSubjectId());
        self::assertSame('system', $persisted->getActorType());
        self::assertNull($persisted->getActorId());
        self::assertSame(['source' => 'unit'], $persisted->getMetadata());
    }

    public function testLogLeadCreatedSkipsWhenNoId(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $lead = new Lead(new City('NoId'), (new Service())->setName('X'), 'Owner', 'owner@example.com');

        $this->em->expects($this->never())->method('persist');
        $repo->logLeadCreated($lead);
        $this->addToAssertionCount(1);
    }

    public function testLogLeadClaimedPersistsWithActor(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $lead = new Lead(new City('Sofia'), (new Service())->setName('Bath'), 'Owner', 'owner@example.com');
        $leadRef = new \ReflectionProperty($lead, 'id');
        $leadRef->setAccessible(true);
        $leadRef->setValue($lead, 11);

        $recipient = new LeadRecipient($lead, 'claim@example.com', 'hash', new \DateTimeImmutable('+1 day'));
        $recRef = new \ReflectionProperty($recipient, 'id');
        $recRef->setAccessible(true);
        $recRef->setValue($recipient, 22);

        $persisted = null;
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persisted): void { $persisted = $entity; });

        $repo->logLeadClaimed($lead, $recipient, AuditLog::ACTOR_GROOMER, 999);

        self::assertInstanceOf(AuditLog::class, $persisted);
        self::assertSame('lead.claimed', $persisted->getEvent());
        self::assertSame(AuditLog::SUBJECT_LEAD, $persisted->getSubjectType());
        self::assertSame(11, $persisted->getSubjectId());
        self::assertSame('groomer', $persisted->getActorType());
        self::assertSame(999, $persisted->getActorId());
        self::assertSame([
            'recipientId' => 22,
            'recipientEmail' => 'claim@example.com',
        ], $persisted->getMetadata());
    }

    public function testLogLeadClaimedSkipsWhenMissingIds(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $lead = new Lead(new City('Varna'), (new Service())->setName('X'), 'Owner', 'owner@example.com');
        $recipient = new LeadRecipient($lead, 'x@example.com', 'hash', new \DateTimeImmutable('+1 day'));

        $this->em->expects($this->never())->method('persist');
        $repo->logLeadClaimed($lead, $recipient);
        $this->addToAssertionCount(1);
    }

    public function testLogRecipientUnsubscribedPersists(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $lead = new Lead(new City('Ruse'), (new Service())->setName('Groom'), 'Owner', 'owner@example.com');
        $leadRef = new \ReflectionProperty($lead, 'id');
        $leadRef->setAccessible(true);
        $leadRef->setValue($lead, 77);

        $recipient = new LeadRecipient($lead, 'to@example.com', 'hash', new \DateTimeImmutable('+1 day'));
        $recRef = new \ReflectionProperty($recipient, 'id');
        $recRef->setAccessible(true);
        $recRef->setValue($recipient, 88);

        $persisted = null;
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persisted): void { $persisted = $entity; });

        $repo->logRecipientUnsubscribed($recipient);

        self::assertInstanceOf(AuditLog::class, $persisted);
        self::assertSame('email.unsubscribed', $persisted->getEvent());
        self::assertSame(AuditLog::SUBJECT_EMAIL, $persisted->getSubjectType());
        self::assertSame(88, $persisted->getSubjectId());
        self::assertSame([
            'leadId' => 77,
            'recipientEmail' => 'to@example.com',
        ], $persisted->getMetadata());
    }

    public function testLogRecipientUnsubscribedSkipsWhenNoRecipientId(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $lead = new Lead(new City('Plovdiv'), (new Service())->setName('Y'), 'Owner', 'owner@example.com');
        $recipient = new LeadRecipient($lead, 'none@example.com', 'hash', new \DateTimeImmutable('+1 day'));

        $this->em->expects($this->never())->method('persist');
        $repo->logRecipientUnsubscribed($recipient);
        $this->addToAssertionCount(1);
    }

    public function testLogUnsubscribedEmailWithoutRecipient(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $persisted = null;
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persisted): void { $persisted = $entity; });

        $repo->logUnsubscribedEmail('lost@example.com');

        self::assertInstanceOf(AuditLog::class, $persisted);
        self::assertSame('email.unsubscribed', $persisted->getEvent());
        self::assertSame(AuditLog::SUBJECT_EMAIL, $persisted->getSubjectType());
        self::assertSame(0, $persisted->getSubjectId());
        self::assertSame(['recipientEmail' => 'lost@example.com'], $persisted->getMetadata());
    }

    public function testLogLeadReleasedPersists(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $lead = new Lead(new City('Burgas'), (new Service())->setName('Trim'), 'Owner', 'owner@example.com');
        $leadRef = new \ReflectionProperty($lead, 'id');
        $leadRef->setAccessible(true);
        $leadRef->setValue($lead, 9001);

        $recipient = new LeadRecipient($lead, 'rel@example.com', 'hash', new \DateTimeImmutable('+1 day'));
        $recRef = new \ReflectionProperty($recipient, 'id');
        $recRef->setAccessible(true);
        $recRef->setValue($recipient, 101);

        $persisted = null;
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persisted): void { $persisted = $entity; });

        $repo->logLeadReleased($lead, $recipient);

        self::assertInstanceOf(AuditLog::class, $persisted);
        self::assertSame('release_success', $persisted->getEvent());
        self::assertSame(AuditLog::SUBJECT_LEAD, $persisted->getSubjectType());
        self::assertSame(9001, $persisted->getSubjectId());
        self::assertSame('groomer', $persisted->getActorType());
        self::assertNull($persisted->getActorId());
        self::assertSame([
            'recipientId' => 101,
            'recipientEmail' => 'rel@example.com',
        ], $persisted->getMetadata());
    }
    public function testLogLeadDispatchSummaryPersistsAuditEntry(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $lead = new Lead(new City('Denver'), (new Service())->setName('Mobile Dog Grooming'), 'Owner', 'owner@example.com');
        // Simulate persisted entity ID
        $ref = new \ReflectionProperty($lead, 'id');
        $ref->setAccessible(true);
        $ref->setValue($lead, 123);

        $persisted = null;
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persisted): void {
                $persisted = $entity;
            });

        $repo->logLeadDispatchSummary($lead, 2, 1, 1, 3);

        self::assertInstanceOf(AuditLog::class, $persisted);
        self::assertSame('lead.dispatch_summary', $persisted->getEvent());
        self::assertSame(AuditLog::SUBJECT_LEAD, $persisted->getSubjectType());
        self::assertSame(123, $persisted->getSubjectId());
        $meta = $persisted->getMetadata();
        self::assertSame(2, $meta['createdRecipients'] ?? null);
        self::assertSame(1, $meta['emailsSent'] ?? null);
        self::assertSame(1, $meta['emailsFailed'] ?? null);
        self::assertSame(3, $meta['skippedExisting'] ?? null);
    }

    public function testLogOutreachEmailSentAndFailed(): void
    {
        $repo = new class($this->em, $this->registry) extends AuditLogRepository {
            public function __construct(private EntityManagerInterface $testEm, ManagerRegistry $registry) { parent::__construct($registry); }
            public function getEntityManager(): EntityManagerInterface { return $this->testEm; }
        };

        $lead = new Lead(new City('Ruse'), (new Service())->setName('Mobile Dog Grooming'), 'Owner', 'owner@example.com');
        $leadRef = new \ReflectionProperty($lead, 'id');
        $leadRef->setAccessible(true);
        $leadRef->setValue($lead, 55);

        $recipient = new LeadRecipient($lead, 'to@example.com', 'hash', new \DateTimeImmutable('+1 day'));
        $recRef = new \ReflectionProperty($recipient, 'id');
        $recRef->setAccessible(true);
        $recRef->setValue($recipient, 77);

        $persisted = [];
        $this->em->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persisted): void {
                $persisted[] = $entity;
            });

        $repo->logOutreachEmailSent($lead, $recipient);
        $repo->logOutreachEmailFailed($lead, $recipient, 'SMTP failure');

        self::assertCount(2, $persisted);
        self::assertSame('outreach_email_sent', $persisted[0]->getEvent());
        self::assertSame('outreach_email_failed', $persisted[1]->getEvent());
        self::assertSame(['leadId' => 55, 'recipientEmail' => 'to@example.com'], $persisted[0]->getMetadata());
        self::assertSame(77, $persisted[0]->getSubjectId());
        self::assertSame(
            ['leadId' => 55, 'recipientEmail' => 'to@example.com', 'error' => 'SMTP failure'],
            $persisted[1]->getMetadata()
        );
    }
}
