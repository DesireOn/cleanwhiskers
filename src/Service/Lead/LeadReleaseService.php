<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Entity\LeadRecipient;
use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final class LeadReleaseService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function release(Lead $lead, LeadRecipient $recipient): LeadReleaseResult
    {
        $leadId = $lead->getId();
        $recipientId = $recipient->getId();
        if ($leadId === null || $recipientId === null) {
            return LeadReleaseResult::invalidRecipient();
        }

        $outcome = null;
        $this->em->getConnection()->transactional(function () use ($leadId, $recipientId, &$outcome): void {
            /** @var Lead|null $lockedLead */
            $lockedLead = $this->em->find(Lead::class, $leadId, LockMode::PESSIMISTIC_WRITE);
            if (!$lockedLead instanceof Lead) {
                $outcome = LeadReleaseResult::invalidRecipient();
                return;
            }

            if ($lockedLead->getStatus() !== Lead::STATUS_CLAIMED) {
                $outcome = LeadReleaseResult::notClaimed();
                return;
            }

            /** @var LeadRecipient|null $managedRecipient */
            $managedRecipient = $this->em->find(LeadRecipient::class, $recipientId);
            if (!$managedRecipient instanceof LeadRecipient || $managedRecipient->getLead()->getId() !== $lockedLead->getId()) {
                $outcome = LeadReleaseResult::invalidRecipient();
                return;
            }

            if ($managedRecipient->getReleasedAt() !== null) {
                $outcome = LeadReleaseResult::alreadyReleased();
                return;
            }

            $now = new DateTimeImmutable();
            $allowedUntil = $managedRecipient->getReleaseAllowedUntil();
            if ($allowedUntil === null || $allowedUntil->getTimestamp() < $now->getTimestamp()) {
                $outcome = LeadReleaseResult::windowExpired();
                return;
            }

            // Perform release
            $lockedLead->setStatus(Lead::STATUS_PENDING);
            $lockedLead->setClaimedBy(null);
            $lockedLead->setClaimedAt(null);
            $lockedLead->setUpdatedAt($now);

            $managedRecipient->setReleasedAt($now);

            $this->em->flush();
            $outcome = LeadReleaseResult::success();
        });

        \assert($outcome instanceof LeadReleaseResult);
        return $outcome;
    }
}

