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
