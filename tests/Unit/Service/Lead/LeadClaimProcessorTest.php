<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service as GroomingService;
use App\Repository\AuditLogRepository;
use App\Service\Lead\LeadClaimProcessor;
use App\Service\Lead\LeadClaimProcessorResult;
use App\Service\Lead\LeadClaimResult;
use App\Service\Lead\LeadClaimService;
use App\Service\Lead\LeadUrlBuilder;
use App\Service\Lead\OutreachEmailFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class LeadClaimProcessorTest extends TestCase
{
    /** @var AuditLogRepository&MockObject */
    private AuditLogRepository $auditLogs;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;
    /** @var MailerInterface&MockObject */
    private MailerInterface $mailer;
    private LeadUrlBuilder $urlBuilder;
    private OutreachEmailFactory $emailFactory;

    protected function setUp(): void
    {
        // claimService is instantiated per-test with a mocked EntityManager
        $this->auditLogs = $this->createMock(AuditLogRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->urlBuilder = new LeadUrlBuilder(new \Symfony\Component\HttpFoundation\UriSigner('test_secret'), 'https://example.test');
        $this->emailFactory = new OutreachEmailFactory('from@example.com', 'CleanWhiskers');
    }

    public function testAlreadyClaimedBySameRecipientShowsSuccess(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);
        $recipient->setClaimedAt(new \DateTimeImmutable('-1 minute'));
        $lead->setStatus(Lead::STATUS_CLAIMED); // will cause claim() to return already_claimed

        $this->auditLogs->expects(self::once())->method('log')->with(
            $this->equalTo('claim_attempt'),
            $this->anything(),
            $this->anything(),
            $this->arrayHasKey('recipientId'),
            $this->anything(),
            $this->anything(),
        );
        $this->em->expects(self::once())->method('flush');

        // Real LeadClaimService configured to return already_claimed because lead is already claimed
        $conn = $this->createMock(\Doctrine\DBAL\Connection::class);
        $conn->method('transactional')->willReturnCallback(fn(callable $fn) => $fn($conn));
        $this->em->method('getConnection')->willReturn($conn);
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });
        $claimService = new LeadClaimService($this->em, $this->createMock(\App\Repository\LeadRepository::class), $this->createMock(\App\Repository\LeadRecipientRepository::class));

        $processor = new LeadClaimProcessor($claimService, $this->auditLogs, $this->em, $this->logger, $this->mailer, $this->urlBuilder, $this->emailFactory);
        $res = $processor->process($lead, $recipient);

        self::assertInstanceOf(LeadClaimProcessorResult::class, $res);
        self::assertSame('already_claimed_by_you', $res->getCode());
    }

    public function testAlreadyClaimedByAnotherLogsConflict(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);
        // recipient not previously claimed, but lead already claimed by someone else
        $lead->setStatus(Lead::STATUS_CLAIMED);

        // Capture events
        $events = [];
        $this->auditLogs->method('log')->willReturnCallback(function (string $event, string $subjectType, int $subjectId, array $metadata) use (&$events) {
            $events[] = $event;
            return new \App\Entity\AuditLog($event, 'system', null, $subjectType, $subjectId, $metadata);
        });
        $this->em->expects(self::atLeast(2))->method('flush');

        $conn = $this->createMock(\Doctrine\DBAL\Connection::class);
        $conn->method('transactional')->willReturnCallback(fn(callable $fn) => $fn($conn));
        $this->em->method('getConnection')->willReturn($conn);
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });
        $claimService = new LeadClaimService($this->em, $this->createMock(\App\Repository\LeadRepository::class), $this->createMock(\App\Repository\LeadRecipientRepository::class));

        $processor = new LeadClaimProcessor($claimService, $this->auditLogs, $this->em, $this->logger, $this->mailer, $this->urlBuilder, $this->emailFactory);
        $res = $processor->process($lead, $recipient);

        self::assertSame('already_claimed', $res->getCode());
        self::assertContains('claim_attempt', $events);
        self::assertContains('claim_conflict', $events);
    }

    public function testSuccessLogsAndSendsEmail(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();
        $this->setId($lead, 10);
        $this->setId($recipient, 5);

        $conn = $this->createMock(\Doctrine\DBAL\Connection::class);
        $conn->method('transactional')->willReturnCallback(fn(callable $fn) => $fn($conn));
        $this->em->method('getConnection')->willReturn($conn);
        $this->em->method('find')->willReturnCallback(function (string $cls, int $id) use ($lead, $recipient) {
            return $cls === Lead::class ? $lead : $recipient;
        });
        $claimService = new LeadClaimService($this->em, $this->createMock(\App\Repository\LeadRepository::class), $this->createMock(\App\Repository\LeadRecipientRepository::class));

        // attempt + success
        $events = [];
        $this->auditLogs->method('log')->willReturnCallback(function (string $event, string $subjectType, int $subjectId, array $metadata) use (&$events) {
            $events[] = $event;
            return new \App\Entity\AuditLog($event, 'system', null, $subjectType, $subjectId, $metadata);
        });
        $this->em->expects(self::atLeast(2))->method('flush');

        // Just ensure send() is called once; email content built by real factory
        $this->mailer->expects(self::once())->method('send')->with($this->isInstanceOf(Email::class));

        $processor = new LeadClaimProcessor($claimService, $this->auditLogs, $this->em, $this->logger, $this->mailer, $this->urlBuilder, $this->emailFactory);
        $res = $processor->process($lead, $recipient);

        self::assertSame('success', $res->getCode());
        self::assertContains('claim_attempt', $events);
        self::assertContains('claim_success', $events);
    }

    /**
     * @return array{0: Lead, 1: LeadRecipient}
     */
    private function makeLeadAndRecipient(): array
    {
        $city = new City('Sofia');
        $service = (new GroomingService())->setName('Mobile Dog Grooming');
        $lead = new Lead($city, $service, 'Owner', 'owner@example.com');
        $recipient = new LeadRecipient($lead, 'invitee@example.com', hash('sha256', 'tok'), new \DateTimeImmutable('+1 hour'));
        return [$lead, $recipient];
    }

    private function setId(object $entity, int $id): void
    {
        $ref = new \ReflectionClass($entity);
        while (!$ref->hasProperty('id') && ($ref = $ref->getParentClass())) {}
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($entity, $id);
    }
}
