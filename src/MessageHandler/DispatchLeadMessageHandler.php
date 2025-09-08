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
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FeatureFlags;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\UriSigner;

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
    ) {
    }

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

        if ($lead->getStatus() !== Lead::STATUS_PENDING) {
            $this->logger->info('Lead dispatch aborted: lead not pending', [
                'leadId' => $message->getLeadId(),
                'status' => $lead->getStatus(),
            ]);
            return;
        }

        // Run segmentation to find matching recipients
        $segmentation = $this->segmentation->findMatchingRecipients($lead);

        // Build a set of existing recipient emails for this lead (normalized)
        $existing = $this->leadRecipientRepository->findByLead($lead);
        $existingEmails = [];
        foreach ($existing as $r) {
            $existingEmails[mb_strtolower(trim($r->getEmail()))] = true;
        }

        $created = 0;
        $newlyCreated = [];
        foreach ($segmentation->getRecipients() as $row) {
            $email = mb_strtolower(trim($row['email']));
            if ('' === $email) {
                continue;
            }

            // Double-check suppression at insert time
            if ($this->emailSuppressions->isSuppressed($email)) {
                $this->logger->info('Skipping suppressed recipient email', [
                    'leadId' => $lead->getId(),
                    'email' => $email,
                ]);
                continue;
            }

            if (isset($existingEmails[$email])) {
                continue; // already have this recipient for this lead
            }

            // Compute claim token and expiry
            $raw = bin2hex(random_bytes(16));
            $hash = hash('sha256', $raw);
            $expiresAt = (new \DateTimeImmutable())->modify('+' . max(1, $this->claimTtlMinutes) . ' minutes');

            $recipient = new \App\Entity\LeadRecipient($lead, $email, $hash, $expiresAt);
            $recipient->setGroomerProfile($row['profile']);
            $this->em->persist($recipient);
            $existingEmails[$email] = true;
            $created++;
            // Keep the raw token so we can include it in the outreach email
            $newlyCreated[] = [
                'entity' => $recipient,
                'rawToken' => $raw,
            ];
        }

        if ($created > 0) {
            $this->em->flush();
        }

        // Send outreach emails to newly created recipients
        $sent = 0;
        $failed = 0;
        foreach ($newlyCreated as $entry) {
            /** @var \App\Entity\LeadRecipient $recipient */
            $recipient = $entry['entity'];
            $rawToken = $entry['rawToken'];

            try {
                $claimUrl = $this->buildSignedClaimUrl((int) ($lead->getId() ?? 0), (int) ($recipient->getId() ?? 0), $recipient->getEmail(), $rawToken, $recipient->getTokenExpiresAt());
                $unsubUrl = $this->buildSignedUnsubscribeUrl($recipient->getEmail());

                $subject = sprintf('New %s lead in %s', $lead->getService()->getName(), $lead->getCity()->getName());
                $text = $this->renderPlainTextEmail($lead, $claimUrl, $unsubUrl, $recipient->getTokenExpiresAt());

                $message = (new Email())
                    ->from($this->outreachFrom)
                    ->to($recipient->getEmail())
                    ->subject($subject)
                    ->text($text);

                // Mark as sent before sending; revert on failure in catch
                $recipient->setStatus(\App\Entity\LeadRecipient::STATUS_SENT);
                $recipient->setInviteSentAt(new \DateTimeImmutable());

                $this->mailer->send($message);
                $sent++;

                // Log successful outreach email in AuditLog via repository helper
                $this->auditLogs->logOutreachEmailSent($lead, $recipient);
            } catch (\Throwable $e) {
                $failed++;
                // Revert status if sending failed
                $recipient->setStatus(\App\Entity\LeadRecipient::STATUS_QUEUED);
                $recipient->setInviteSentAt(null);
                $this->logger->error('Failed sending outreach email', [
                    'leadId' => $lead->getId(),
                    'recipientId' => $recipient->getId(),
                    'email' => $recipient->getEmail(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($sent > 0) {
            $this->em->flush();
        }

        $this->logger->info('Lead dispatch processed', [
            'leadId' => $lead->getId(),
            'createdRecipients' => $created,
            'emailsSent' => $sent,
            'emailsFailed' => $failed,
            'skippedExisting' => count($segmentation->getRecipients()) - $created,
        ]);
    }

    private function buildSignedClaimUrl(int $leadId, int $recipientId, string $email, string $rawToken, \DateTimeImmutable $expiresAt): string
    {
        $query = http_build_query([
            'lid' => (int) $leadId,
            'rid' => (int) $recipientId,
            'email' => $email,
            'token' => $rawToken,
            'exp' => $expiresAt->getTimestamp(),
        ]);
        $url = rtrim($this->appBaseUrl, '/') . '/leads/claim?' . $query;
        return $this->uriSigner->sign($url);
    }

    private function buildSignedUnsubscribeUrl(string $email): string
    {
        $query = http_build_query(['email' => $email]);
        $url = rtrim($this->appBaseUrl, '/') . '/unsubscribe?' . $query;
        return $this->uriSigner->sign($url);
    }

    private function renderPlainTextEmail(Lead $lead, string $claimUrl, string $unsubUrl, \DateTimeImmutable $expiresAt): string
    {
        $lines = [];
        $lines[] = sprintf('You have a new %s lead in %s.', $lead->getService()->getName(), $lead->getCity()->getName());
        $lines[] = '';
        $lines[] = sprintf('Claim this lead: %s', $claimUrl);
        $lines[] = sprintf('This claim link expires on %s.', $expiresAt->format('Y-m-d H:i T'));
        $lines[] = '';
        $lines[] = sprintf('If you no longer wish to receive outreach emails, unsubscribe here: %s', $unsubUrl);
        $lines[] = '';
        $lines[] = sprintf('— %s', $this->companyName);
        return implode("\n", $lines);
    }
}
