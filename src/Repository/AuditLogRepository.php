<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AuditLog;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuditLog>
 */
class AuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
    }

    /**
     * Generic logger: persists an AuditLog without flushing.
     *
     * @param array<string,mixed> $metadata
     */
    public function log(string $event, string $subjectType, int $subjectId, array $metadata = [], string $actorType = AuditLog::ACTOR_SYSTEM, ?int $actorId = null): AuditLog
    {
        $log = new AuditLog(
            event: $event,
            actorType: $actorType,
            actorId: $actorId,
            subjectType: $subjectType,
            subjectId: $subjectId,
            metadata: $metadata,
        );
        $this->getEntityManager()->persist($log);
        return $log;
    }

    public function logOutreachEmailSent(Lead $lead, LeadRecipient $recipient): void
    {
        if ($lead->getId() === null || $recipient->getId() === null) {
            return; // IDs required for logging
        }

        $this->log(
            event: 'outreach_email_sent',
            subjectType: AuditLog::SUBJECT_EMAIL,
            subjectId: (int) $recipient->getId(),
            metadata: [
                'leadId' => $lead->getId(),
                'recipientEmail' => $recipient->getEmail(),
            ],
        );
    }

    public function logOutreachEmailFailed(Lead $lead, LeadRecipient $recipient, string $error): void
    {
        if ($lead->getId() === null || $recipient->getId() === null) {
            return;
        }

        $this->log(
            event: 'outreach_email_failed',
            subjectType: AuditLog::SUBJECT_EMAIL,
            subjectId: (int) $recipient->getId(),
            metadata: [
                'leadId' => $lead->getId(),
                'recipientEmail' => $recipient->getEmail(),
                'error' => $error,
            ],
        );
    }

    /**
     * Log that a lead was created in the system.
     *
     * @param array<string,mixed> $metadata
     */
    public function logLeadCreated(Lead $lead, array $metadata = [], string $actorType = AuditLog::ACTOR_SYSTEM, ?int $actorId = null): void
    {
        if ($lead->getId() === null) {
            return;
        }

        $this->log(
            event: 'lead.created',
            subjectType: AuditLog::SUBJECT_LEAD,
            subjectId: (int) $lead->getId(),
            metadata: $metadata,
            actorType: $actorType,
            actorId: $actorId,
        );
    }

    public function logLeadClaimed(Lead $lead, LeadRecipient $recipient, string $actorType = AuditLog::ACTOR_GROOMER, ?int $actorId = null): void
    {
        if ($lead->getId() === null || $recipient->getId() === null) {
            return;
        }

        $this->log(
            event: 'lead.claimed',
            subjectType: AuditLog::SUBJECT_LEAD,
            subjectId: (int) $lead->getId(),
            metadata: [
                'recipientId' => $recipient->getId(),
                'recipientEmail' => $recipient->getEmail(),
            ],
            actorType: $actorType,
            actorId: $actorId,
        );
    }

    public function logLeadDispatchSummary(Lead $lead, int $createdRecipients, int $emailsSent, int $emailsFailed, int $skippedExisting): void
    {
        if ($lead->getId() === null) {
            return;
        }

        $this->log(
            event: 'lead.dispatch_summary',
            subjectType: AuditLog::SUBJECT_LEAD,
            subjectId: (int) $lead->getId(),
            metadata: [
                'createdRecipients' => $createdRecipients,
                'emailsSent' => $emailsSent,
                'emailsFailed' => $emailsFailed,
                'skippedExisting' => $skippedExisting,
            ],
        );
    }

    public function logRecipientUnsubscribed(LeadRecipient $recipient): void
    {
        if ($recipient->getId() === null) {
            return;
        }

        $lead = $recipient->getLead();
        $this->log(
            event: 'email.unsubscribed',
            subjectType: AuditLog::SUBJECT_EMAIL,
            subjectId: (int) $recipient->getId(),
            metadata: [
                'leadId' => $lead->getId(),
                'recipientEmail' => $recipient->getEmail(),
            ],
        );
    }

    public function logUnsubscribedEmail(string $email): void
    {
        // When no recipient context exists, use subjectId 0 and store email in metadata
        $this->log(
            event: 'email.unsubscribed',
            subjectType: AuditLog::SUBJECT_EMAIL,
            subjectId: 0,
            metadata: [
                'recipientEmail' => $email,
            ],
        );
    }

    public function logLeadReleased(Lead $lead, LeadRecipient $recipient): void
    {
        if ($lead->getId() === null || $recipient->getId() === null) {
            return;
        }

        $this->log(
            event: 'release_success',
            subjectType: AuditLog::SUBJECT_LEAD,
            subjectId: (int) $lead->getId(),
            metadata: [
                'recipientId' => $recipient->getId(),
                'recipientEmail' => $recipient->getEmail(),
            ],
            actorType: AuditLog::ACTOR_GROOMER,
            actorId: null,
        );
    }
}
