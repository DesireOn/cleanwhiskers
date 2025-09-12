<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\AuditLog;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;

final class LeadClaimProcessor
{
    public function __construct(
        private readonly LeadClaimService $claimService,
        private readonly AuditLogRepository $auditLogs,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly LeadUrlBuilder $urlBuilder,
        private readonly OutreachEmailFactory $emailFactory,
    ) {}

    public function process(Lead $lead, LeadRecipient $recipient): LeadClaimProcessorResult
    {
        // Audit: record attempt (guest)
        if ($lead->getId() !== null && $recipient->getId() !== null) {
            $this->auditLogs->log(
                event: 'claim_attempt',
                subjectType: AuditLog::SUBJECT_LEAD,
                subjectId: (int) $lead->getId(),
                metadata: [
                    'recipientId' => $recipient->getId(),
                    'recipientEmail' => $recipient->getEmail(),
                    'guest' => true,
                ],
                actorType: AuditLog::ACTOR_GROOMER,
                actorId: null,
            );
            $this->em->flush();
        }

        // Attempt claim atomically
        $result = $this->claimService->claim($lead, $recipient, null);

        if (!$result->isSuccess()) {
            $code = $result->getCode();
            // Same recipient already claimed earlier: re-show success
            if ($code === 'already_claimed' && $recipient->getClaimedAt() !== null) {
                return LeadClaimProcessorResult::alreadyClaimedByYou($lead, $recipient);
            }

            // Audit conflict
            if ($code === 'already_claimed' && $lead->getId() !== null && $recipient->getId() !== null) {
                $this->auditLogs->log(
                    event: 'claim_conflict',
                    subjectType: AuditLog::SUBJECT_LEAD,
                    subjectId: (int) $lead->getId(),
                    metadata: [
                        'recipientId' => $recipient->getId(),
                        'recipientEmail' => $recipient->getEmail(),
                        'groomerId' => null,
                        'leadStatus' => $lead->getStatus(),
                        'claimedById' => $lead->getClaimedBy()?->getId(),
                    ],
                    actorType: AuditLog::ACTOR_GROOMER,
                    actorId: null,
                );
                $this->em->flush();
            }

            $this->logger->info('Lead claim failed', [
                'groomerId' => null,
                'leadId' => $lead->getId(),
                'recipientId' => $recipient->getId(),
                'code' => $code,
            ]);

            return $code === 'already_claimed'
                ? LeadClaimProcessorResult::alreadyClaimed($lead, $recipient)
                : LeadClaimProcessorResult::invalidRecipient($lead, $recipient);
        }

        // Success: audit and notify
        if ($lead->getId() !== null && $recipient->getId() !== null) {
            $this->auditLogs->log(
                event: 'claim_success',
                subjectType: AuditLog::SUBJECT_LEAD,
                subjectId: (int) $lead->getId(),
                metadata: [
                    'recipientId' => $recipient->getId(),
                    'recipientEmail' => $recipient->getEmail(),
                    'groomerId' => null,
                    'guest' => true,
                ],
                actorType: AuditLog::ACTOR_GROOMER,
                actorId: null,
            );
            $this->em->flush();
        }

        try {
            $unsubUrl = $this->urlBuilder->buildSignedUnsubscribeUrl($recipient->getEmail());
            $email = $this->emailFactory->buildLeadClaimSuccessEmail($lead, $recipient->getEmail(), null, $unsubUrl);
            $this->mailer->send($email);
        } catch (\Throwable $e) {
            $this->logger->error('Failed sending claim success email', [
                'leadId' => $lead->getId(),
                'recipientId' => $recipient->getId(),
                'email' => $recipient->getEmail(),
                'error' => $e->getMessage(),
            ]);
        }

        return LeadClaimProcessorResult::success($lead, $recipient);
    }
}

