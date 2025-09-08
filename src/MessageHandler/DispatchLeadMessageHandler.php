<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Lead;
use App\Entity\AuditLog;
use App\Repository\AuditLogRepository;
use App\Message\DispatchLeadMessage;
use App\Repository\LeadRepository;
use App\Repository\LeadRecipientRepository;
use App\Repository\EmailSuppressionRepository;
use App\Service\Lead\LeadSegmentationService;
use App\Service\Lead\LeadRecipientTokenFactory;
use App\Service\Lead\LeadUrlBuilder;
use App\Service\Lead\OutreachEmailFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FeatureFlags;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\UriSigner;
use App\Service\Lead\LeadRecipientInvite;

#[AsMessageHandler]
final class DispatchLeadMessageHandler
{
    public function __construct(
        private readonly FeatureFlags $featureFlags,
        private readonly LeadRepository $leadRepository,
        private readonly LeadRecipientRepository $leadRecipientRepository,
        private readonly EmailSuppressionRepository $emailSuppressions,
        private readonly LeadSegmentationService $segmentation,
        private readonly AuditLogRepository $auditLogs,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly UriSigner $uriSigner,
        private readonly string $appBaseUrl = 'http://localhost',
        private readonly string $outreachFrom = 'support@example.com',
        private readonly string $companyName = 'CleanWhiskers',
        private readonly int $claimTtlMinutes = 60,
        ?LeadRecipientTokenFactory $recipientTokenFactory = null,
        ?LeadUrlBuilder $urlBuilder = null,
        ?OutreachEmailFactory $emailFactory = null,
    ) {
        $this->recipientTokenFactory = $recipientTokenFactory ?? new LeadRecipientTokenFactory();
        $this->urlBuilder = $urlBuilder ?? new LeadUrlBuilder($this->uriSigner, $this->appBaseUrl);
        $this->emailFactory = $emailFactory ?? new OutreachEmailFactory($this->outreachFrom, $this->companyName);
    }

    private LeadRecipientTokenFactory $recipientTokenFactory;
    private LeadUrlBuilder $urlBuilder;
    private OutreachEmailFactory $emailFactory;

    public function __invoke(DispatchLeadMessage $message): void
    {
        if (!$this->featureFlags->isLeadsEnabled()) {
            $this->logger->info('Lead dispatch aborted: feature flag disabled', [
                'leadId' => $message->getLeadId(),
            ]);
            return;
        }

        $lead = $this->leadRepository->find($message->getLeadId());
        if ($lead === null) {
            $this->logger->warning('Lead dispatch aborted: lead not found', [
                'leadId' => $message->getLeadId(),
            ]);
            return;
        }

        if (!$this->isLeadPending($lead)) {
            $this->logger->info('Lead dispatch aborted: lead not pending', [
                'leadId' => $message->getLeadId(),
                'status' => $lead->getStatus(),
            ]);
            return;
        }

        $segmentationResult = $this->segmentation->findMatchingRecipients($lead);
        $existingEmails = $this->loadExistingEmails($lead);

        [$newlyCreated, $createdCount] = $this->createRecipients($lead, $segmentationResult->getRecipients(), $existingEmails);
        if ($createdCount > 0) {
            $this->em->flush();
        }

        [$sent, $failed] = $this->sendInvites($lead, $newlyCreated);
        if ($sent > 0) {
            $this->em->flush();
        }

        $skipped = count($segmentationResult->getRecipients()) - $createdCount;
        $this->logger->info('Lead dispatch processed', [
            'leadId' => $lead->getId(),
            'createdRecipients' => $createdCount,
            'emailsSent' => $sent,
            'emailsFailed' => $failed,
            'skippedExisting' => $skipped,
        ]);

        $this->auditLogs->logLeadDispatchSummary(
            $lead,
            createdRecipients: $createdCount,
            emailsSent: $sent,
            emailsFailed: $failed,
            skippedExisting: $skipped,
        );
    }

    private function isLeadPending(Lead $lead): bool
    {
        return $lead->getStatus() === Lead::STATUS_PENDING;
    }

    /**
     * @return array<string, true> Normalized set of existing recipient emails for the lead
     */
    private function loadExistingEmails(Lead $lead): array
    {
        $existing = $this->leadRecipientRepository->findByLead($lead);
        $emails = [];
        foreach ($existing as $r) {
            $emails[$this->normalizeEmail($r->getEmail())] = true;
        }
        return $emails;
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /**
     * @param array<int, array{profile: mixed, email: string, score?: float, reasons?: array<mixed>}> $candidates
     * @param array<string, true> $existingEmails
     * @return array{0: array<int, LeadRecipientInvite>, 1: int}
     */
    private function createRecipients(Lead $lead, array $candidates, array &$existingEmails): array
    {
        $created = 0;
        $newlyCreated = [];

        foreach ($candidates as $row) {
            $normalizedEmail = $this->normalizeEmail($row['email'] ?? '');
            if ($normalizedEmail === '') {
                continue;
            }

            if ($this->emailSuppressions->isSuppressed($normalizedEmail)) {
                $this->logger->info('Skipping suppressed recipient email', [
                    'leadId' => $lead->getId(),
                    'email' => $normalizedEmail,
                ]);
                continue;
            }

            if (isset($existingEmails[$normalizedEmail])) {
                continue;
            }

            [$hash, $raw, $expiresAt] = $this->recipientTokenFactory->issue($this->claimTtlMinutes);

            $recipient = new \App\Entity\LeadRecipient($lead, $normalizedEmail, $hash, $expiresAt);
            if (isset($row['profile'])) {
                $recipient->setGroomerProfile($row['profile']);
            }

            $this->em->persist($recipient);
            $existingEmails[$normalizedEmail] = true;
            $created++;

            $newlyCreated[] = new LeadRecipientInvite($recipient, $raw);
        }

        return [$newlyCreated, $created];
    }

    /**
     * @param array<int, LeadRecipientInvite> $newlyCreated
     * @return array{0:int,1:int} [sent, failed]
     */
    private function sendInvites(Lead $lead, array $newlyCreated): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($newlyCreated as $entry) {
            $recipient = $entry->recipient;
            $rawToken = $entry->rawToken;

            try {
                $claimUrl = $this->urlBuilder->buildSignedClaimUrl((int) ($lead->getId() ?? 0), (int) ($recipient->getId() ?? 0), $recipient->getEmail(), $rawToken, $recipient->getTokenExpiresAt());
                $unsubUrl = $this->urlBuilder->buildSignedUnsubscribeUrl($recipient->getEmail());

                $email = $this->emailFactory->buildLeadInviteEmail($lead, $recipient->getEmail(), $claimUrl, $unsubUrl, $recipient->getTokenExpiresAt());

                $recipient->setStatus(\App\Entity\LeadRecipient::STATUS_SENT);
                $recipient->setInviteSentAt(new \DateTimeImmutable());

                $this->mailer->send($email);
                $sent++;

                $this->auditLogs->logOutreachEmailSent($lead, $recipient);
            } catch (\Throwable $e) {
                $failed++;
                $recipient->setStatus(\App\Entity\LeadRecipient::STATUS_QUEUED);
                $recipient->setInviteSentAt(null);
                $this->logger->error('Failed sending outreach email', [
                    'leadId' => $lead->getId(),
                    'recipientId' => $recipient->getId(),
                    'email' => $recipient->getEmail(),
                    'error' => $e->getMessage(),
                ]);
                $this->auditLogs->logOutreachEmailFailed($lead, $recipient, $e->getMessage());
            }
        }

        return [$sent, $failed];
    }

    // Token generation, URL building, and email rendering are delegated to services.
}
