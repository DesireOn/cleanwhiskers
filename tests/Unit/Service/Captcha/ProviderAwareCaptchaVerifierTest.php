<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Captcha;

use App\Service\Captcha\CaptchaVerifierInterface;
use App\Service\Captcha\HcaptchaVerifier;
use App\Service\Captcha\NullCaptchaVerifier;
use App\Service\Captcha\ProviderAwareCaptchaVerifier;
use App\Service\Captcha\RecaptchaVerifier;
use PHPUnit\Framework\TestCase;

final class ProviderAwareCaptchaVerifierTest extends TestCase
{
    public function testDelegatesToRecaptcha(): void
    {
        $null = new NullCaptchaVerifier(false);
        $recaptcha = $this->createMock(RecaptchaVerifier::class);
        $hcaptcha = $this->createMock(HcaptchaVerifier::class);

        $recaptcha->expects(self::once())
            ->method('verify')
            ->with('t', 'ip')
            ->willReturn(true);

        $verifier = new ProviderAwareCaptchaVerifier('recaptcha', $null, $recaptcha, $hcaptcha);
        self::assertTrue($verifier->verify('t', 'ip'));
    }

    public function testDelegatesToGoogleAlias(): void
    {
        $null = new NullCaptchaVerifier(false);
        $recaptcha = $this->createMock(RecaptchaVerifier::class);
        $hcaptcha = $this->createMock(HcaptchaVerifier::class);

        $recaptcha->expects(self::once())
            ->method('verify')
            ->with('t2', 'ip2')
            ->willReturn(true);

        $verifier = new ProviderAwareCaptchaVerifier('google', $null, $recaptcha, $hcaptcha);
        self::assertTrue($verifier->verify('t2', 'ip2'));
    }

    public function testDelegatesToHcaptcha(): void
    {
        $null = new NullCaptchaVerifier(false);
        $recaptcha = $this->createMock(RecaptchaVerifier::class);
        $hcaptcha = $this->createMock(HcaptchaVerifier::class);

        $hcaptcha->expects(self::once())
            ->method('verify')
            ->with('x', 'y')
            ->willReturn(true);

        $verifier = new ProviderAwareCaptchaVerifier('hcaptcha', $null, $recaptcha, $hcaptcha);
        self::assertTrue($verifier->verify('x', 'y'));
    }

    public function testFallsBackToNullWhenNoneOrUnknown(): void
    {
        $null = new NullCaptchaVerifier(false);
        $verifierNone = new ProviderAwareCaptchaVerifier('none', $null);

        // NullCaptchaVerifier(false) always returns true
        self::assertTrue($verifierNone->verify('anything', 'ip'));

        $verifierUnknown = new ProviderAwareCaptchaVerifier('foobar', $null);
        self::assertTrue($verifierUnknown->verify('anything', 'ip'));
    }

    public function testReturnsFalseIfProviderUnavailable(): void
    {
        $null = new NullCaptchaVerifier(true); // when "enabled" and no token -> should require non-empty token

        // Provider is set to recaptcha but no RecaptchaVerifier is injected
        $verifier = new ProviderAwareCaptchaVerifier('recaptcha', $null, null, null);
        self::assertFalse($verifier->verify('token', 'ip'));
    }
}

