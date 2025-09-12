<?php

declare(strict_types=1);

namespace App\Service\Captcha;

final class ProviderAwareCaptchaVerifier implements CaptchaVerifierInterface
{
    public function __construct(
        private readonly string $provider,
        private readonly NullCaptchaVerifier $nullVerifier,
        private readonly ?RecaptchaVerifier $recaptchaVerifier = null,
        private readonly ?HcaptchaVerifier $hcaptchaVerifier = null,
    ) {
    }

    public function verify(?string $token, ?string $clientIp = null): bool
    {
        $provider = strtolower(trim($this->provider));
        return match ($provider) {
            'recaptcha', 'google' => $this->recaptchaVerifier?->verify($token, $clientIp) ?? false,
            'hcaptcha' => $this->hcaptchaVerifier?->verify($token, $clientIp) ?? false,
            default => $this->nullVerifier->verify($token, $clientIp),
        };
    }
}

