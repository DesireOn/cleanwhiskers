<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use Symfony\Component\HttpFoundation\UriSigner;

final class LeadReleaseValidator
{
    public function __construct(
        private readonly UriSigner $signer,
        private readonly LeadRepository $leads,
        private readonly LeadRecipientRepository $recipients,
    ) {}

    public function validate(LeadReleaseRequest $req): LeadReleaseValidationResult
    {
        if (!ctype_digit($req->lid) || !ctype_digit($req->rid) || !ctype_digit($req->exp)) {
            return LeadReleaseValidationResult::missingParams();
        }

        $lead = $this->leads->find((int) $req->lid);
        $recipient = $this->recipients->find((int) $req->rid);
        if (!$lead instanceof Lead || $recipient === null || $recipient->getLead()->getId() !== $lead->getId()) {
            return LeadReleaseValidationResult::notFound();
        }

        $isSigned = $this->signer->check($req->uri);
        $expValid = (int) $req->exp >= $req->nowTs;
        if (!$isSigned) {
            return LeadReleaseValidationResult::invalidSignature();
        }
        if (!$expValid) {
            return LeadReleaseValidationResult::expired();
        }

        return LeadReleaseValidationResult::ok($lead, $recipient);
    }
}

