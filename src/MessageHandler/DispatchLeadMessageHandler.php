<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Lead;
use App\Message\DispatchLeadMessage;
use App\Repository\LeadRepository;
use App\Service\FeatureFlags;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DispatchLeadMessageHandler
{
    public function __construct(
        private readonly FeatureFlags $featureFlags,
        private readonly LeadRepository $leadRepository,
        private readonly LoggerInterface $logger,
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

        // Further dispatch logic goes here (e.g., segmentation, notifications)
    }
}

