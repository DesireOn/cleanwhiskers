<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Repository\LeadRepository;
use App\Repository\LeadRecipientRepository;
use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final class LeadClaimService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LeadRepository $leads,
        private readonly LeadRecipientRepository $recipients,
    ) {
    }

    public function claim(Lead $lead, LeadRecipient $recipient, ?GroomerProfile $groomer): LeadClaimResult
    {
        $leadId = $lead->getId();
        $recipientId = $recipient->getId();

        if ($leadId === null || $recipientId === null) {
            return LeadClaimResult::invalidRecipient();
        }

        $outcome = null;

        $this->em->getConnection()->transactional(function () use ($leadId, $recipientId, $groomer, &$outcome): void {
            /** @var Lead|null $lockedLead */
            $lockedLead = $this->em->find(Lead::class, $leadId, LockMode::PESSIMISTIC_WRITE);
            if ($lockedLead === null) {
                $outcome = LeadClaimResult::invalidRecipient();
                return;
            }

            if ($lockedLead->getStatus() !== Lead::STATUS_PENDING || $lockedLead->getClaimedBy() !== null) {
                $outcome = LeadClaimResult::alreadyClaimed();
                return;
            }

            /** @var LeadRecipient|null $managedRecipient */
            $managedRecipient = $this->em->find(LeadRecipient::class, $recipientId);
            if ($managedRecipient === null || $managedRecipient->getLead()->getId() !== $lockedLead->getId()) {
                $outcome = LeadClaimResult::invalidRecipient();
                return;
            }

            // Perform the claim
            $now = new DateTimeImmutable();
            $lockedLead->setStatus(Lead::STATUS_CLAIMED);
            // Allow guest claims: when $groomer is null, we still mark the lead as claimed
            // so it cannot be claimed again. claimedBy may remain null in that case.
            $lockedLead->setClaimedBy($groomer);
            $lockedLead->setClaimedAt($now);
            $lockedLead->setUpdatedAt($now);

            // Associate the recipient with the groomer that claimed it (optional but useful)
            // Link recipient to claiming groomer when available (nullable for guest claims)
            $managedRecipient->setGroomerProfile($groomer);
            $managedRecipient->setClaimedAt($now);

            $this->em->flush();

            $outcome = LeadClaimResult::success($lockedLead);
        });

        \assert($outcome instanceof LeadClaimResult);
        return $outcome;
    }
}
