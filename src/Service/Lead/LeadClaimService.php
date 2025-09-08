<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class LeadClaimService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AuditLogRepository $auditLogs,
        private readonly LoggerInterface $logger,
    ) {}

    public function claim(LeadRecipient $recipient, string $rawToken, ?\DateTimeImmutable $now = null): LeadClaimResult
    {
        $now = $now ?? new \DateTimeImmutable();
        $lead = $recipient->getLead();

        if ($lead->getStatus() !== Lead::STATUS_PENDING) {
            return LeadClaimResult::fail('lead_not_pending', 'Lead is not in a claimable state.', [
                'status' => $lead->getStatus(),
            ]);
        }

        // Verify token expiry
        if ($recipient->getTokenExpiresAt() <= $now) {
            return LeadClaimResult::fail('expired', 'Claim token has expired.');
        }

        // Verify token hash
        $computed = hash('sha256', $rawToken);
        if (!hash_equals($recipient->getClaimTokenHash(), $computed)) {
            return LeadClaimResult::fail('mismatch', 'Invalid claim token.');
        }

        // Ensure we have a groomer profile context
        $profile = $recipient->getGroomerProfile();
        if ($profile === null) {
            return LeadClaimResult::fail('no_profile', 'Recipient is not associated with a groomer profile.');
        }

        // Perform the claim
        $lead->setStatus(Lead::STATUS_CLAIMED);
        $lead->setClaimedBy($profile);
        $lead->setClaimedAt($now);

        try {
            $this->em->persist($lead);
            $this->em->flush();
        } catch (\Throwable $e) {
            $this->logger->error('Failed persisting lead claim', [
                'leadId' => $lead->getId(),
                'recipientId' => $recipient->getId(),
                'error' => $e->getMessage(),
            ]);
            return LeadClaimResult::fail('persistence_error', 'Failed to persist claim. Please retry later.');
        }

        // Log the claim after persistence succeeds (IDs should be present)
        $this->auditLogs->logLeadClaimed($lead, $recipient);

        return LeadClaimResult::ok($lead, $recipient);
    }
}

