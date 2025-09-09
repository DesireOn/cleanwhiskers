<?php

declare(strict_types=1);

namespace App\Service\Lead;

use Symfony\Component\HttpFoundation\UriSigner;

final class LeadUrlBuilder
{
    public function __construct(
        private readonly UriSigner $signer,
        private readonly string $appBaseUrl,
    ) {}

    public function buildSignedClaimUrl(int $leadId, int $recipientId, string $email, string $rawToken, \DateTimeImmutable $expiresAt): string
    {
        $query = http_build_query([
            'lid' => (int) $leadId,
            'rid' => (int) $recipientId,
            'email' => $email,
            'token' => $rawToken,
            'exp' => $expiresAt->getTimestamp(),
        ]);
        $url = rtrim($this->appBaseUrl, '/') . '/leads/claim?' . $query;
        return $this->signer->sign($url);
    }

    public function buildSignedUnsubscribeUrl(string $email): string
    {
        // Long-lived unsubscribe link (1 year)
        $expires = (new \DateTimeImmutable('+1 year'))->getTimestamp();
        $query = http_build_query([
            'email' => $email,
            'expires' => $expires,
        ]);
        $url = rtrim($this->appBaseUrl, '/') . '/unsubscribe?' . $query;
        return $this->signer->sign($url);
    }
}
