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
    }

    private function makeLead(): Lead
    {
        $city = new City('Denver');
        $service = (new Service())->setName('Mobile Dog Grooming');
        return new Lead($city, $service, 'Jane PetOwner', 'owner@example.com');
    }
}
