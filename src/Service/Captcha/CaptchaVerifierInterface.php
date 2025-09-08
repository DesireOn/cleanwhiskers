<?php

declare(strict_types=1);

namespace App\Service\Captcha;

interface CaptchaVerifierInterface
{
    /**
     * Verify a CAPTCHA token for a given client IP.
     */
    public function verify(?string $token, ?string $clientIp = null): bool;
}

