<?php

declare(strict_types=1);

namespace App\Service\Captcha;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RecaptchaVerifier implements CaptchaVerifierInterface
{
    private string $endpoint;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $secretKey,
        ?string $endpoint = null,
    ) {
        $this->endpoint = $endpoint ?: 'https://www.google.com/recaptcha/api/siteverify';
    }

    public function verify(?string $token, ?string $clientIp = null): bool
    {
        if (!is_string($token) || trim($token) === '') {
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', $this->endpoint, [
                'body' => array_filter([
                    'secret' => $this->secretKey,
                    'response' => $token,
                    'remoteip' => $clientIp,
                ]),
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = $response->toArray(false);

            return isset($data['success']) && $data['success'] === true;
        } catch (\Throwable) {
            return false;
        }
    }
}

