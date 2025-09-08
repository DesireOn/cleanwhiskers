<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Dto\Lead\LeadSubmissionDto;
use App\Entity\Lead;
use App\Message\DispatchLeadMessage;
use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class LeadSubmissionService
{
    public function __construct(
        private readonly CityRepository $cities,
        private readonly ServiceRepository $services,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $bus,
        private readonly LeadTokenFactory $leadTokenFactory,
    ) {
    }

    public function create(LeadSubmissionDto $dto): Lead
    {
        $city = $this->cities->findOneBySlug($dto->citySlug);
        $service = $this->services->findOneBySlug($dto->serviceSlug);

        if (null === $city || null === $service) {
            throw new \InvalidArgumentException('Invalid city or service.');
        }

        $email = ($dto->email !== null && trim($dto->email) !== '') ? $dto->email : 'owner@invalid.local';

        $lead = new Lead($city, $service, $dto->fullName, $email);
        $lead->setPhone($dto->phone);
        $lead->setPetType($dto->petType);
        $lead->setBreedSize($dto->breedSize !== '' ? $dto->breedSize : null);
        $lead->setConsentToShare($dto->consentToShare);

        // Issue owner token
        $this->leadTokenFactory->issueOwnerToken($lead);

        // Persist
        $this->em->persist($lead);
        $this->em->flush();

        // Async dispatch to process lead
        $this->bus->dispatch(new DispatchLeadMessage((int) $lead->getId()));

        return $lead;
    }
}

