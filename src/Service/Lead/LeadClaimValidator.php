<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use Symfony\Component\HttpFoundation\UriSigner;

final class LeadClaimValidator
{
    public function __construct(
        private readonly UriSigner $signer,
        private readonly LeadRepository $leads,
        private readonly LeadRecipientRepository $recipients,
    ) {}

    public function validate(LeadClaimRequest $req): LeadClaimValidationResult
    {
        // Parameters presence validation first
        if (!ctype_digit($req->lid) || !ctype_digit($req->rid) || $req->email === '' || $req->token === '') {
            return LeadClaimValidationResult::missingParams();
        }

        $lead = $this->leads->find((int) $req->lid);
        $recipient = $this->recipients->find((int) $req->rid);
        if (!$lead instanceof Lead || $recipient === null || $recipient->getLead()->getId() !== $lead->getId()) {
            return LeadClaimValidationResult::notFound();
        }

        // Top-level signed URL and external expiry gate
        $isSigned = $this->signer->check($req->uri);
        $expValid = ctype_digit($req->exp) && (int) $req->exp >= $req->nowTs;

        // Recipient token/email checks
        $normalizedEmail = mb_strtolower(trim($req->email));
        $hashOk = hash('sha256', $req->token) === $recipient->getClaimTokenHash();
        $emailOk = $normalizedEmail !== '' && $normalizedEmail === mb_strtolower($recipient->getEmail());

        // Recipient-specific token expiry
        $recipientNotExpired = $recipient->getTokenExpiresAt()->getTimestamp() >= $req->nowTs;

        // If signature or exp invalid, allow a graceful re-show when this recipient already claimed before
        if ((!$isSigned || !$expValid)) {
            if ($hashOk && $emailOk && $recipient->getClaimedAt() !== null) {
                return LeadClaimValidationResult::allowGuestReshow($lead, $recipient);
            }
            return $isSigned ? LeadClaimValidationResult::expired($lead, $recipient) : LeadClaimValidationResult::invalidSignature($lead, $recipient);
        }

        // Now enforce token/email and recipient expiry
        if (!$hashOk || !$emailOk) {
            return LeadClaimValidationResult::invalidTokenOrEmail($lead, $recipient);
        }
        if (!$recipientNotExpired) {
            return LeadClaimValidationResult::recipientExpired($lead, $recipient);
        }

        return LeadClaimValidationResult::ok($lead, $recipient);
    }
}

