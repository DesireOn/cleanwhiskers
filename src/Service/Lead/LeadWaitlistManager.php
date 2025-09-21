<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\AuditLog;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\LeadWaitlistEntry;
use App\Repository\LeadWaitlistEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

final class LeadWaitlistManager
{
    public function __construct(
        private readonly LeadWaitlistEntryRepository $waitlists,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Adds the recipient to the waitlist for the given lead if not already present.
     *
     * @return array{created: bool, entry: LeadWaitlistEntry}
     */
    public function join(Lead $lead, LeadRecipient $recipient): array
    {
        $existing = $this->waitlists->findOneBy(['lead' => $lead, 'leadRecipient' => $recipient]);
        if ($existing instanceof LeadWaitlistEntry) {
            return ['created' => false, 'entry' => $existing];
        }

        $entry = new LeadWaitlistEntry($lead, $recipient);
        $this->em->persist($entry);

        // Bookkeeping/audit log
        $this->em->persist(new AuditLog(
            event: 'waitlist_joined',
            actorType: AuditLog::ACTOR_GROOMER,
            actorId: $recipient->getGroomerProfile()?->getId(),
            subjectType: AuditLog::SUBJECT_LEAD,
            subjectId: (int) $lead->getId(),
            metadata: [
                'recipientId' => $recipient->getId(),
                'email' => $recipient->getEmail(),
            ],
        ));

        $this->em->flush();

        return ['created' => true, 'entry' => $entry];
    }
}

