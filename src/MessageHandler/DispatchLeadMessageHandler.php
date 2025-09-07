<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Lead;
use App\Message\DispatchLeadMessage;
use App\Repository\LeadRepository;
use App\Repository\LeadRecipientRepository;
use App\Repository\EmailSuppressionRepository;
use App\Service\Lead\LeadSegmentationService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FeatureFlags;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DispatchLeadMessageHandler
{
    public function __construct(
        private readonly FeatureFlags $featureFlags,
        private readonly LeadRepository $leadRepository,
        private readonly LeadRecipientRepository $leadRecipientRepository,
        private readonly EmailSuppressionRepository $emailSuppressions,
        private readonly LeadSegmentationService $segmentation,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
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
        }

        if ($created > 0) {
            $this->em->flush();
        }

        $this->logger->info('Lead dispatch processed', [
            'leadId' => $lead->getId(),
            'createdRecipients' => $created,
            'skippedExisting' => count($segmentation->getRecipients()) - $created,
        ]);
    }
}
