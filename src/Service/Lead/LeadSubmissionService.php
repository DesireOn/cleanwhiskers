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
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

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
        $email = mb_strtolower(trim($email));

        // Compute idempotency fingerprint for identical submissions
        $fingerprint = $this->computeFingerprint(
            $email,
            (string) ($city->getId() ?? ''),
            (string) ($service->getId() ?? ''),
            (string) ($dto->phone ?? ''),
            (string) ($dto->petType ?? ''),
            (string) ($dto->breedSize ?? '')
        );

        // If an identical submission already exists, return it without dispatching again
        $existing = $this->em->getRepository(\App\Entity\Lead::class)->findOneBy(['submissionFingerprint' => $fingerprint]);
        if ($existing instanceof \App\Entity\Lead) {
            // Re-dispatch to ensure outreach in case prior attempt didn’t process
            if ($existing->getId() !== null) {
                $this->bus->dispatch(new DispatchLeadMessage((int) $existing->getId()));
            }
            return $existing;
        }

        $lead = new Lead($city, $service, $dto->fullName, $email);
        $lead->setPhone($dto->phone);
        $lead->setPetType($dto->petType);
        $lead->setBreedSize($dto->breedSize !== '' ? $dto->breedSize : null);
        $lead->setConsentToShare($dto->consentToShare);
        $lead->setSubmissionFingerprint($fingerprint);

        // Issue owner token
        $this->leadTokenFactory->issueOwnerToken($lead);

        // Persist (guard against race by catching unique violation)
        try {
            $this->em->persist($lead);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            /** @var \App\Entity\Lead|null $dupe */
            $dupe = $this->em->getRepository(\App\Entity\Lead::class)->findOneBy(['submissionFingerprint' => $fingerprint]);
            if ($dupe instanceof \App\Entity\Lead) {
                if ($dupe->getId() !== null) {
                    $this->bus->dispatch(new DispatchLeadMessage((int) $dupe->getId()));
                }
                return $dupe;
            }
            throw $e;
        }

        // Async dispatch to process lead
        $this->bus->dispatch(new DispatchLeadMessage((int) $lead->getId()));

        return $lead;
    }

    private function computeFingerprint(string $email, string $cityId, string $serviceId, string $phone, string $petType, string $breedSize): string
    {
        $parts = [
            trim(mb_strtolower($email)),
            trim($cityId),
            trim($serviceId),
            trim(preg_replace('/\D+/', '', $phone) ?? ''), // numbers only for phone
            trim(mb_strtolower($petType)),
            trim(mb_strtolower($breedSize)),
        ];
        return hash('sha256', implode('|', $parts));
    }
}
