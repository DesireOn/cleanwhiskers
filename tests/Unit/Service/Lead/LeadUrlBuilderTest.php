<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Service\Lead\LeadUrlBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\UriSigner;

final class LeadUrlBuilderTest extends TestCase
{
    /** @var UriSigner&MockObject */
    private UriSigner $signer;

    protected function setUp(): void
    {
        $this->signer = $this->createMock(UriSigner::class);
    }

    public function testBuildSignedClaimUrl(): void
    {
        $builder = new LeadUrlBuilder($this->signer, 'https://example.test');

        $this->signer->expects($this->once())
            ->method('sign')
            ->with($this->callback(function (string $url): bool {
                // Basic shape and params exist before signing
                self::assertStringStartsWith('https://example.test/leads/claim?', $url);
                self::assertStringContainsString('lid=10', $url);
                self::assertStringContainsString('rid=5', $url);
                self::assertStringContainsString('email=foo%40bar.com', $url);
                self::assertStringContainsString('token=abc', $url);
                self::assertStringContainsString('exp=', $url);
                return true;
            }))
            ->willReturnCallback(static fn(string $url): string => $url.'#sig');

        $expires = new \DateTimeImmutable('+1 hour');
        $signed = $builder->buildSignedClaimUrl(10, 5, 'foo@bar.com', 'abc', $expires);

        self::assertStringEndsWith('#sig', $signed);
    }

    public function testBuildSignedUnsubscribeUrl(): void
    {
        $builder = new LeadUrlBuilder($this->signer, 'https://example.test/');

        $this->signer->expects($this->once())
            ->method('sign')
            ->with($this->stringStartsWith('https://example.test/unsubscribe?email=foo%40bar.com'))
            ->willReturnCallback(static fn(string $url): string => $url.'#sig');

        $url = $builder->buildSignedUnsubscribeUrl('foo@bar.com');
        self::assertStringEndsWith('#sig', $url);
    }
}

