<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Captcha;

use App\Service\Captcha\HcaptchaVerifier;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HcaptchaVerifierTest extends TestCase
{
    public function testVerifyReturnsTrueOnSuccess(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $http->expects(self::once())
            ->method('request')
            ->with(
                'POST',
                'https://hcaptcha.com/siteverify',
                self::callback(function (array $options): bool {
                    $this->assertSame('application/x-www-form-urlencoded', $options['headers']['Content-Type']);
                    $this->assertSame('secret-key', $options['body']['secret']);
                    $this->assertSame('the-token', $options['body']['response']);
                    $this->assertSame('127.0.0.1', $options['body']['remoteip']);
                    return true;
                })
            )
            ->willReturn($response);

        $response->expects(self::once())
            ->method('toArray')
            ->with(false)
            ->willReturn(['success' => true]);

        $verifier = new HcaptchaVerifier($http, 'secret-key');
        self::assertTrue($verifier->verify('the-token', '127.0.0.1'));
    }

    public function testVerifyReturnsFalseOnEmptyToken(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $verifier = new HcaptchaVerifier($http, 'secret-key');

        self::assertFalse($verifier->verify(null, '127.0.0.1'));
        self::assertFalse($verifier->verify('', '127.0.0.1'));
        self::assertFalse($verifier->verify('   ', '127.0.0.1'));
    }

    public function testVerifyReturnsFalseOnFailureResponse(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $http->expects(self::once())
            ->method('request')
            ->willReturn($response);

        $response->expects(self::once())
            ->method('toArray')
            ->with(false)
            ->willReturn(['success' => false]);

        $verifier = new HcaptchaVerifier($http, 'secret-key');
        self::assertFalse($verifier->verify('token', '127.0.0.1'));
    }

    public function testVerifyReturnsFalseOnHttpException(): void
    {
        $http = $this->createMock(HttpClientInterface::class);

        $http->expects(self::once())
            ->method('request')
            ->willThrowException(new \RuntimeException('network error'));

        $verifier = new HcaptchaVerifier($http, 'secret-key');
        self::assertFalse($verifier->verify('token', '127.0.0.1'));
    }
}

