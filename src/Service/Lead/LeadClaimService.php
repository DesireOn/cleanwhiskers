<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use Doctrine\ORM\EntityManagerInterface;

final class LeadClaimService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LeadRepository $leads,
        private readonly LeadRecipientRepository $recipients,
    ) {
    }

    /**
     * Attempts to claim the lead atomically.
     * Returns true for winner, false if already claimed (loser).
     */
    public function claim(string $leadToken, string $recipientToken): bool
    {
        $lead = $this->leads->findOneByClaimToken($leadToken);
        if (null === $lead) {
            throw new \RuntimeException('Lead not found.');
        }

        $recipient = $this->recipients->findOneByRecipientToken($recipientToken);
        if (null === $recipient) {
            throw new \RuntimeException('Recipient not found.');
        }

        if ($recipient->getLead()->getId() !== $lead->getId()) {
            throw new \RuntimeException('Recipient does not match the lead.');
        }

        // Atomic update: only claim if NEW and claimedBy is NULL
        $qb = $this->em->createQueryBuilder()
            ->update(Lead::class, 'l')
            ->set('l.claimedBy', ':groomer')
            ->set('l.claimedAt', ':now')
            ->set('l.status', ':claimed')
            ->where('l = :lead')
            ->andWhere('l.status = :new')
            ->andWhere('l.claimedBy IS NULL')
            ->setParameter('groomer', $recipient->getGroomer())
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('claimed', Lead::STATUS_CLAIMED)
            ->setParameter('new', Lead::STATUS_NEW)
            ->setParameter('lead', $lead);

        $affected = $qb->getQuery()->execute();

        return 1 === $affected;
    }
}

