<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service;
use App\Message\DispatchLeadMessage;
use App\MessageHandler\DispatchLeadMessageHandler;
use App\Repository\EmailSuppressionRepository;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use App\Service\FeatureFlags;
use App\Service\Lead\LeadSegmentationResult;
use App\Service\Lead\LeadSegmentationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mailer\MailerInterface;

final class DispatchLeadMessageHandlerTest extends TestCase
{
    private FeatureFlags $flags;
    /** @var LeadRepository&MockObject */
    private LeadRepository $leads;
    /** @var LeadRecipientRepository&MockObject */
    private LeadRecipientRepository $recipients;
    /** @var EmailSuppressionRepository&MockObject */
    private EmailSuppressionRepository $suppressions;
    /** @var LeadSegmentationService&MockObject */
    private LeadSegmentationService $segmentation;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;
    /** @var MailerInterface&MockObject */
    private MailerInterface $mailer;
    /** @var UriSigner&MockObject */
    private UriSigner $signer;

    protected function setUp(): void
    {
        $this->flags = new FeatureFlags(true);
        $this->leads = $this->createMock(LeadRepository::class);
        $this->recipients = $this->createMock(LeadRecipientRepository::class);
        $this->suppressions = $this->createMock(EmailSuppressionRepository::class);
        $this->segmentation = $this->createMock(LeadSegmentationService::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->signer = $this->createMock(UriSigner::class);
    }

    private function handler(): DispatchLeadMessageHandler
    {
        return new DispatchLeadMessageHandler(
            $this->flags,
            $this->leads,
            $this->recipients,
            $this->suppressions,
            $this->segmentation,
            $this->em,
            $this->logger,
            $this->mailer,
            $this->signer,
            'http://localhost',
            'support@example.com',
            'CleanWhiskers',
            60,
        );
    }

    public function testDoesNothingWhenFeatureDisabled(): void
    {
        $this->flags = new FeatureFlags(false);
        $this->leads->expects($this->never())->method('find');
        $this->segmentation->expects($this->never())->method('findMatchingRecipients');
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');
        $this->mailer->expects($this->never())->method('send');

        ($this->handler())(new DispatchLeadMessage(123));
        $this->addToAssertionCount(1); // If no expectations failed, pass
    }

    public function testAbortsWhenLeadNotFound(): void
    {
        $this->flags = new FeatureFlags(true);
        $this->leads->method('find')->with(123)->willReturn(null);
        $this->segmentation->expects($this->never())->method('findMatchingRecipients');
        $this->em->expects($this->never())->method('persist');
        $this->mailer->expects($this->never())->method('send');

        ($this->handler())(new DispatchLeadMessage(123));
        $this->addToAssertionCount(1);
    }

    public function testAbortsWhenLeadNotPending(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_CLAIMED);

        $this->flags = new FeatureFlags(true);
        $this->leads->method('find')->with(5)->willReturn($lead);
        $this->segmentation->expects($this->never())->method('findMatchingRecipients');
        $this->em->expects($this->never())->method('persist');
        $this->mailer->expects($this->never())->method('send');

        ($this->handler())(new DispatchLeadMessage(5));
        $this->addToAssertionCount(1);
    }

    public function testCreatesRecipientsSendsEmailsAndUpdatesStatus(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_PENDING);

        $this->flags = new FeatureFlags(true);
        $this->leads->method('find')->willReturn($lead);

        // One existing recipient with duplicate email we should skip
        $existing = new LeadRecipient($lead, 'dupe@example.com', 'hash', (new \DateTimeImmutable())->modify('+1 day'));
        $this->recipients->method('findByLead')->with($lead)->willReturn([$existing]);

        // Segmentation returns: suppressed@example.com (should skip), dupe@example.com (skip duplicate), good@example.com (process)
        $segResult = new LeadSegmentationResult();
        $profile = $this->getMockBuilder(\App\Entity\GroomerProfile::class)->disableOriginalConstructor()->onlyMethods(['getUser'])->getMock();
        $segResult->addRecipient($profile, 'suppressed@example.com', 0.9, []);
        $segResult->addRecipient($profile, 'dupe@example.com', 0.8, []);
        $segResult->addRecipient($profile, 'good@example.com', 0.7, []);
        $this->segmentation->method('findMatchingRecipients')->with($lead)->willReturn($segResult);

        $this->suppressions->method('isSuppressed')->willReturnCallback(static function (string $email): bool {
            return $email === 'suppressed@example.com';
        });

        // Capture persisted entity, ensure it's updated to SENT after email
        $persisted = null;
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$persisted): void {
            $persisted = $entity;
        });
        // Two flushes: one after creating recipients, one after marking sent
        $this->em->expects($this->exactly(2))->method('flush');

        // Signer returns same URL with #signed
        $this->signer->method('sign')->willReturnCallback(static function (string $url): string {
            return $url . '#sig';
        });

        // Mailer should send exactly once for good@example.com
        $this->mailer->expects($this->once())->method('send');

        ($this->handler())(new DispatchLeadMessage(42));

        $this->assertInstanceOf(LeadRecipient::class, $persisted);
        $this->assertSame('good@example.com', $persisted->getEmail());
        // Invite timestamp set indicates successful send; status change is handled in persistence layer
        $this->assertInstanceOf(\DateTimeImmutable::class, $persisted->getInviteSentAt());
        $this->assertSame(LeadRecipient::STATUS_SENT, $persisted->getStatus());
    }

    public function testSkipsDuplicateEmailsWithCaseAndWhitespaceDifferences(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_PENDING);

        $this->leads->method('find')->willReturn($lead);

        // Existing recipient stored lowercased and trimmed
        $existing = new LeadRecipient($lead, 'normalized@example.com', 'hash', (new \DateTimeImmutable())->modify('+1 day'));
        $this->recipients->method('findByLead')->with($lead)->willReturn([$existing]);

        // Segmentation yields same email but with different case/whitespace, and one new
        $segResult = new LeadSegmentationResult();
        $profile = $this->getMockBuilder(\App\Entity\GroomerProfile::class)->disableOriginalConstructor()->onlyMethods(['getUser'])->getMock();
        $segResult->addRecipient($profile, '  Normalized@Example.com  ', 0.9, []); // should be treated as duplicate
        $segResult->addRecipient($profile, 'new@example.com', 0.8, []);
        $this->segmentation->method('findMatchingRecipients')->with($lead)->willReturn($segResult);

        $this->suppressions->method('isSuppressed')->willReturn(false);

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$persisted): void {
            if ($entity instanceof LeadRecipient) {
                $persisted[] = $entity;
            }
        });
        $this->em->expects($this->exactly(2))->method('flush');

        $this->signer->method('sign')->willReturnCallback(static fn(string $url): string => $url);
        $this->mailer->expects($this->once())->method('send');

        ($this->handler())(new DispatchLeadMessage(2001));

        $this->assertCount(1, $persisted, 'Only new@example.com should be persisted');
        $this->assertSame('new@example.com', $persisted[0]->getEmail());
    }

    public function testSkipsEmptyEmailsAndAvoidsUnnecessaryFlushWhenNoneCreated(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_PENDING);

        $this->leads->method('find')->willReturn($lead);
        $this->recipients->method('findByLead')->with($lead)->willReturn([]);

        $segResult = new LeadSegmentationResult();
        $profile = $this->getMockBuilder(\App\Entity\GroomerProfile::class)->disableOriginalConstructor()->onlyMethods(['getUser'])->getMock();
        $segResult->addRecipient($profile, '   ', 0.9, []); // will be trimmed to empty and skipped
        $this->segmentation->method('findMatchingRecipients')->with($lead)->willReturn($segResult);

        $this->suppressions->method('isSuppressed')->willReturn(false);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');
        $this->mailer->expects($this->never())->method('send');

        ($this->handler())(new DispatchLeadMessage(3001));

        $this->addToAssertionCount(1);
    }

    public function testClaimExpiryUsesMinimumOneMinuteWhenConfiguredZero(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_PENDING);

        $this->leads->method('find')->willReturn($lead);
        $this->recipients->method('findByLead')->with($lead)->willReturn([]);

        $segResult = new LeadSegmentationResult();
        $profile = $this->getMockBuilder(\App\Entity\GroomerProfile::class)->disableOriginalConstructor()->onlyMethods(['getUser'])->getMock();
        $segResult->addRecipient($profile, 'ttl@example.com', 0.9, []);
        $this->segmentation->method('findMatchingRecipients')->with($lead)->willReturn($segResult);

        $this->suppressions->method('isSuppressed')->willReturn(false);

        $captured = null;
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$captured): void {
            if ($entity instanceof LeadRecipient) {
                $captured = $entity;
            }
        });
        $this->em->expects($this->exactly(2))->method('flush');

        $this->signer->method('sign')->willReturnCallback(static fn(string $url): string => $url);
        $this->mailer->expects($this->once())->method('send');

        // Rebuild handler with TTL set to 0 to exercise max(1, ttl)
        $handler = new DispatchLeadMessageHandler(
            $this->flags,
            $this->leads,
            $this->recipients,
            $this->suppressions,
            $this->segmentation,
            $this->em,
            $this->logger,
            $this->mailer,
            $this->signer,
            'http://localhost',
            'support@example.com',
            'CleanWhiskers',
            0,
        );

        $before = new \DateTimeImmutable('+55 seconds');
        $after = new \DateTimeImmutable('+3 minutes');
        $handler(new DispatchLeadMessage(4001));

        $this->assertInstanceOf(LeadRecipient::class, $captured);
        $expires = $captured->getTokenExpiresAt();
        $this->assertGreaterThan($before, $expires, 'Expiry should be at least ~1 minute from now');
        $this->assertLessThan($after, $expires, 'Expiry should not be far beyond a couple of minutes');
    }

    public function testEmailContentIncludesSignedUrlsAndCompanySignature(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_PENDING);

        $this->leads->method('find')->willReturn($lead);
        $this->recipients->method('findByLead')->with($lead)->willReturn([]);

        $segResult = new LeadSegmentationResult();
        $profile = $this->getMockBuilder(\App\Entity\GroomerProfile::class)->disableOriginalConstructor()->onlyMethods(['getUser'])->getMock();
        $segResult->addRecipient($profile, 'content@example.com', 0.9, []);
        $this->segmentation->method('findMatchingRecipients')->with($lead)->willReturn($segResult);

        $this->suppressions->method('isSuppressed')->willReturn(false);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->exactly(2))->method('flush');

        // Record URLs passed to sign() and assert content/order afterwards
        $signedUrls = [];
        $this->signer->expects($this->exactly(2))->method('sign')
            ->willReturnCallback(function (string $url) use (&$signedUrls): string {
                $signedUrls[] = $url;
                return $url . '#sig';
            });

        // Capture the outbound email to verify subject/text
        $capturedEmail = null;
        $this->mailer->expects($this->once())->method('send')
            ->willReturnCallback(function ($email) use (&$capturedEmail): void {
                $capturedEmail = $email;
            });

        ($this->handler())(new DispatchLeadMessage(5001));

        $this->assertNotNull($capturedEmail);
        $this->assertCount(2, $signedUrls);
        $this->assertStringStartsWith('http://localhost/leads/claim?', $signedUrls[0]);
        $this->assertStringStartsWith('http://localhost/unsubscribe?', $signedUrls[1]);
        $this->assertStringContainsString('email=content%40example.com', $signedUrls[1]);
        $subject = $capturedEmail->getSubject();
        $text = $capturedEmail->getTextBody();

        $this->assertStringContainsString('New Mobile Dog Grooming lead in Denver', (string) $subject);
        $this->assertStringContainsString('Claim this lead: http://localhost/leads/claim?', (string) $text);
        $this->assertStringContainsString('#sig', (string) $text, 'Signed URLs should include signature marker');
        $this->assertStringContainsString('unsubscribe here: http://localhost/unsubscribe?', (string) $text);
        $this->assertStringContainsString('— CleanWhiskers', (string) $text);
    }

    public function testMailerFailureRevertsStatusAndLogsError(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_PENDING);

        $this->flags = new FeatureFlags(true);
        $this->leads->method('find')->willReturn($lead);
        $this->recipients->method('findByLead')->with($lead)->willReturn([]);

        $segResult = new LeadSegmentationResult();
        $profile = $this->getMockBuilder(\App\Entity\GroomerProfile::class)->disableOriginalConstructor()->onlyMethods(['getUser'])->getMock();
        $segResult->addRecipient($profile, 'fail@example.com', 0.9, []);
        $this->segmentation->method('findMatchingRecipients')->with($lead)->willReturn($segResult);

        $this->suppressions->method('isSuppressed')->willReturn(false);

        $persisted = null;
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$persisted): void {
            $persisted = $entity;
        });
        // Only one flush (after creation) because send fails (no post-send flush)
        $this->em->expects($this->once())->method('flush');

        $this->signer->method('sign')->willReturnCallback(static function (string $url): string {
            return $url . '#sig';
        });

        // Simulate mailer failure
        $this->mailer->expects($this->once())->method('send')->willThrowException(new \RuntimeException('SMTP failure'));

        // Expect error log with message and context
        $this->logger->expects($this->once())->method('error')
            ->with(
                $this->stringContains('Failed sending outreach email'),
                $this->callback(function ($ctx): bool {
                    return is_array($ctx)
                        && array_key_exists('leadId', $ctx)
                        && array_key_exists('recipientId', $ctx)
                        && array_key_exists('email', $ctx)
                        && array_key_exists('error', $ctx);
                })
            );

        ($this->handler())(new DispatchLeadMessage(77));

        $this->assertInstanceOf(LeadRecipient::class, $persisted);
        $this->assertSame('fail@example.com', $persisted->getEmail());
        $this->assertSame(LeadRecipient::STATUS_QUEUED, $persisted->getStatus());
        $this->assertNull($persisted->getInviteSentAt());
    }

    public function testMultipleRecipientsMixedSuccessAndFailure(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_PENDING);

        $this->flags = new FeatureFlags(true);
        $this->leads->method('find')->willReturn($lead);

        // Existing duplicate email
        $existing = new LeadRecipient($lead, 'dupe@example.com', 'hash', (new \DateTimeImmutable())->modify('+1 day'));
        $this->recipients->method('findByLead')->with($lead)->willReturn([$existing]);

        // Segmentation yields: suppressed, duplicate, ok1, ok2
        $segResult = new LeadSegmentationResult();
        $profile = $this->getMockBuilder(\App\Entity\GroomerProfile::class)->disableOriginalConstructor()->onlyMethods(['getUser'])->getMock();
        $segResult->addRecipient($profile, 'suppressed@example.com', 0.95, []);
        $segResult->addRecipient($profile, 'dupe@example.com', 0.9, []);
        $segResult->addRecipient($profile, 'ok1@example.com', 0.85, []);
        $segResult->addRecipient($profile, 'ok2@example.com', 0.8, []);
        $this->segmentation->method('findMatchingRecipients')->with($lead)->willReturn($segResult);

        $this->suppressions->method('isSuppressed')->willReturnCallback(static function (string $email): bool {
            return $email === 'suppressed@example.com';
        });

        // Capture both persisted recipients
        $persisted = [];
        $this->em->expects($this->exactly(2))->method('persist')->willReturnCallback(function ($entity) use (&$persisted): void {
            if ($entity instanceof LeadRecipient) {
                $persisted[] = $entity;
            }
        });
        // Flush after create and after at least one send success
        $this->em->expects($this->exactly(2))->method('flush');

        // Signer returns signed URL
        $this->signer->method('sign')->willReturnCallback(static function (string $url): string {
            return $url . '#sig';
        });

        // First send succeeds, second fails
        $sendCount = 0;
        $this->mailer->expects($this->exactly(2))->method('send')->willReturnCallback(function () use (&$sendCount): void {
            $sendCount++;
            if ($sendCount === 2) {
                throw new \RuntimeException('SMTP failure');
            }
        });

        // Expect one error log for the failure
        $this->logger->expects($this->once())->method('error')
            ->with($this->stringContains('Failed sending outreach email'));

        ($this->handler())(new DispatchLeadMessage(1001));

        // Verify both persisted recipients by email
        $byEmail = [];
        foreach ($persisted as $r) {
            $byEmail[$r->getEmail()] = $r;
        }
        $this->assertArrayHasKey('ok1@example.com', $byEmail);
        $this->assertArrayHasKey('ok2@example.com', $byEmail);

        $ok1 = $byEmail['ok1@example.com'];
        $ok2 = $byEmail['ok2@example.com'];

        $this->assertInstanceOf(\DateTimeImmutable::class, $ok1->getInviteSentAt(), 'ok1 should have invite timestamp');
        $this->assertNull($ok2->getInviteSentAt(), 'ok2 should not have invite timestamp after failure');
        $this->assertSame(LeadRecipient::STATUS_QUEUED, $ok2->getStatus(), 'ok2 should be reverted to queued');
    }

    private function makeLead(): Lead
    {
        $city = new City('Denver');
        $service = (new Service())->setName('Mobile Dog Grooming');
        return new Lead($city, $service, 'Jane PetOwner', 'owner@example.com');
    }
}
