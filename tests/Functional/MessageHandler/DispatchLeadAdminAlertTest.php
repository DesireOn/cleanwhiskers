<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service;
use App\Message\DispatchLeadMessage;
use App\MessageHandler\DispatchLeadMessageHandler;
use App\Repository\AuditLogRepository;
use App\Repository\EmailSuppressionRepository;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use App\Service\FeatureFlags;
use App\Service\Lead\LeadSegmentationResult;
use App\Service\Lead\LeadSegmentationService;
use App\Tests\Double\ConditionalFailingMailer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;

final class DispatchLeadAdminAlertTest extends KernelTestCase
{
    use MailerAssertionsTrait;

    private int $baselineCount = 0;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->baselineCount = is_array($this->getMailerEvents()) ? count($this->getMailerEvents()) : 0;
    }

    public function testAdminAlertIsSentWhenInvitationFails(): void
    {
        $container = static::getContainer();
        $innerMailer = $container->get(MailerInterface::class);
        $alertsRecipient = (string) ($_ENV['ALERT_EMAIL'] ?? 'alerts@example.com');
        $mailer = new ConditionalFailingMailer($innerMailer, $alertsRecipient);

        // Minimal in-memory setup for handler dependencies
        $flags = new FeatureFlags(true);
        /** @var LeadRepository&MockObject $leads */
        $leads = $this->createMock(LeadRepository::class);
        /** @var LeadRecipientRepository&MockObject $recipients */
        $recipients = $this->createMock(LeadRecipientRepository::class);
        /** @var EmailSuppressionRepository&MockObject $suppressions */
        $suppressions = $this->createMock(EmailSuppressionRepository::class);
        /** @var LeadSegmentationService&MockObject $segmentation */
        $segmentation = $this->createMock(LeadSegmentationService::class);
        /** @var AuditLogRepository&MockObject $auditLogs */
        $auditLogs = $this->createMock(AuditLogRepository::class);
        /** @var EntityManagerInterface&MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var UriSigner&MockObject $signer */
        $signer = $this->createMock(UriSigner::class);

        $lead = new Lead(new City('Denver'), (new Service())->setName('Mobile Dog Grooming'), 'Owner', 'owner@example.com');
        $lead->setStatus(Lead::STATUS_PENDING);

        $leads->method('find')->willReturn($lead);
        $recipients->method('findByLead')->with($lead)->willReturn([]);
        $suppressions->method('isSuppressed')->willReturn(false);
        $signer->method('sign')->willReturnCallback(static fn(string $url): string => $url);

        // One recipient so the invite send will fail and trigger exactly one alert
        $segResult = new LeadSegmentationResult();
        $profile = $this->getMockBuilder(\App\Entity\GroomerProfile::class)->disableOriginalConstructor()->getMock();
        $segResult->addRecipient($profile, 'fail@example.com', 0.9, []);
        $segmentation->method('findMatchingRecipients')->with($lead)->willReturn($segResult);

        // Run handler
        $handler = new DispatchLeadMessageHandler(
            $flags,
            $leads,
            $recipients,
            $suppressions,
            $segmentation,
            $auditLogs,
            $em,
            $logger,
            $mailer,
            $signer,
            'http://localhost',
            'support@example.com',
            'CleanWhiskers',
            60,
            $alertsRecipient,
        );

        $handler(new DispatchLeadMessage(123));

        // Assert exactly one new email (the admin alert) and that it contains failure details
        $events = $this->getMailerEvents();
        $new = array_slice($events, $this->baselineCount);
        self::assertCount(1, $new, 'Exactly one admin alert should be sent');
        $sent = $new[0]->getMessage();

        self::assertSame($alertsRecipient, $sent->getTo()[0]->getAddress());
        self::assertStringContainsString('Alert: 1 outreach email failure(s)', (string) $sent->getSubject());
        $text = (string) $sent->getTextBody();
        self::assertStringContainsString('Emails sent: 0', $text);
        self::assertStringContainsString('Emails failed: 1', $text);
        self::assertStringContainsString('fail@example.com', $text);
        self::assertStringContainsString('Simulated SMTP failure', $text);
    }
}

