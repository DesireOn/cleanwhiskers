<?php

declare(strict_types=1);

namespace App\Service\Captcha;

final class NullCaptchaVerifier implements CaptchaVerifierInterface
{
    public function __construct(private readonly bool $enabled = false)
    {
    }

    public function verify(?string $token, ?string $clientIp = null): bool
    {
        // If CAPTCHA is not enabled, always pass.
        if (!$this->enabled) {
            return true;
        }

        // When enabled but no real verifier is configured, require a non-empty token.
        return is_string($token) && trim($token) !== '';
    }
}

