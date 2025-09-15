<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use App\Service\Lead\LeadReleaseRequest;
use App\Service\Lead\LeadReleaseValidationResult;
use App\Service\Lead\LeadReleaseValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\UriSigner;

final class LeadReleaseValidatorTest extends TestCase
{
    public function testMissingParamsWhenNonDigits(): void
    {
        $signer = $this->createMock(UriSigner::class);
        $leads = $this->createMock(LeadRepository::class);
        $recipients = $this->createMock(LeadRecipientRepository::class);

        $validator = new LeadReleaseValidator($signer, $leads, $recipients);

        $req = LeadReleaseRequest::fromRaw(
            uri: 'https://app.test/leads/release?lid=abc&rid=2&exp=123',
            lid: 'abc', // not all digits
            rid: '2',
            exp: '123',
            nowTs: 1_700_000_000,
        );

        $res = $validator->validate($req);

        self::assertFalse($res->isOk());
        self::assertSame(LeadReleaseValidationResult::MISSING_PARAMS, $res->getCode());
    }

    public function testNotFoundWhenLeadMissing(): void
    {
        $signer = $this->createMock(UriSigner::class);
        $signer->method('check')->willReturn(true);

        $leads = $this->createMock(LeadRepository::class);
        $leads->method('find')->with(10)->willReturn(null);

        $recipients = $this->createMock(LeadRecipientRepository::class);
        $recipients->method('find')->with(20)->willReturn(null);

        $validator = new LeadReleaseValidator($signer, $leads, $recipients);

        $req = LeadReleaseRequest::fromRaw(
            uri: 'https://app.test/leads/release?lid=10&rid=20&exp=1700001000',
            lid: '10',
            rid: '20',
            exp: '1700001000',
            nowTs: 1_700_000_000,
        );

        $res = $validator->validate($req);
        self::assertFalse($res->isOk());
        self::assertSame(LeadReleaseValidationResult::NOT_FOUND, $res->getCode());
    }

    public function testNotFoundWhenRecipientMissing(): void
    {
        $signer = $this->createMock(UriSigner::class);
        $signer->method('check')->willReturn(true);

        $lead = $this->createMock(Lead::class);
        $lead->method('getId')->willReturn(10);

        $leads = $this->createMock(LeadRepository::class);
        $leads->method('find')->with(10)->willReturn($lead);

        $recipients = $this->createMock(LeadRecipientRepository::class);
        $recipients->method('find')->with(20)->willReturn(null);

        $validator = new LeadReleaseValidator($signer, $leads, $recipients);

        $req = LeadReleaseRequest::fromRaw(
            uri: 'https://app.test/leads/release?lid=10&rid=20&exp=1700001000',
            lid: '10',
            rid: '20',
            exp: '1700001000',
            nowTs: 1_700_000_000,
        );

        $res = $validator->validate($req);
        self::assertFalse($res->isOk());
        self::assertSame(LeadReleaseValidationResult::NOT_FOUND, $res->getCode());
    }

    public function testNotFoundWhenRecipientLeadMismatch(): void
    {
        $signer = $this->createMock(UriSigner::class);
        $signer->method('check')->willReturn(true);

        $leadA = $this->createMock(Lead::class);
        $leadA->method('getId')->willReturn(10);

        $leadB = $this->createMock(Lead::class);
        $leadB->method('getId')->willReturn(999);

        $recipient = $this->createMock(LeadRecipient::class);
        $recipient->method('getLead')->willReturn($leadB);

        $leads = $this->createMock(LeadRepository::class);
        $leads->method('find')->with(10)->willReturn($leadA);

        $recipients = $this->createMock(LeadRecipientRepository::class);
        $recipients->method('find')->with(20)->willReturn($recipient);

        $validator = new LeadReleaseValidator($signer, $leads, $recipients);

        $req = LeadReleaseRequest::fromRaw(
            uri: 'https://app.test/leads/release?lid=10&rid=20&exp=1700001000',
            lid: '10',
            rid: '20',
            exp: '1700001000',
            nowTs: 1_700_000_000,
        );

        $res = $validator->validate($req);
        self::assertFalse($res->isOk());
        self::assertSame(LeadReleaseValidationResult::NOT_FOUND, $res->getCode());
    }

    public function testInvalidSignatureWhenCheckFails(): void
    {
        $signer = $this->createMock(UriSigner::class);
        $signer->method('check')->willReturn(false);

        $lead = $this->createMock(Lead::class);
        $lead->method('getId')->willReturn(10);

        $recipient = $this->createMock(LeadRecipient::class);
        $recipient->method('getLead')->willReturn($lead);

        $leads = $this->createMock(LeadRepository::class);
        $leads->method('find')->with(10)->willReturn($lead);

        $recipients = $this->createMock(LeadRecipientRepository::class);
        $recipients->method('find')->with(20)->willReturn($recipient);

        $validator = new LeadReleaseValidator($signer, $leads, $recipients);

        $req = LeadReleaseRequest::fromRaw(
            uri: 'https://app.test/leads/release?lid=10&rid=20&exp=1700001000',
            lid: '10',
            rid: '20',
            exp: '1700001000',
            nowTs: 1_700_000_000,
        );

        $res = $validator->validate($req);

        self::assertFalse($res->isOk());
        self::assertSame(LeadReleaseValidationResult::INVALID_SIGNATURE, $res->getCode());
    }

    public function testExpiredWhenNowExceedsExp(): void
    {
        $signer = $this->createMock(UriSigner::class);
        $signer->method('check')->willReturn(true);

        $lead = $this->createMock(Lead::class);
        $lead->method('getId')->willReturn(10);

        $recipient = $this->createMock(LeadRecipient::class);
        $recipient->method('getLead')->willReturn($lead);

        $leads = $this->createMock(LeadRepository::class);
        $leads->method('find')->with(10)->willReturn($lead);

        $recipients = $this->createMock(LeadRecipientRepository::class);
        $recipients->method('find')->with(20)->willReturn($recipient);

        $validator = new LeadReleaseValidator($signer, $leads, $recipients);

        $now = 1_700_000_000;
        $req = LeadReleaseRequest::fromRaw(
            uri: 'https://app.test/leads/release?lid=10&rid=20&exp=' . ($now - 1),
            lid: '10',
            rid: '20',
            exp: (string) ($now - 1),
            nowTs: $now,
        );

        $res = $validator->validate($req);

        self::assertFalse($res->isOk());
        self::assertSame(LeadReleaseValidationResult::EXPIRED, $res->getCode());
    }

    public function testOkWhenAllValid(): void
    {
        $signer = $this->createMock(UriSigner::class);
        $signer->method('check')->willReturn(true);

        $lead = $this->createMock(Lead::class);
        $lead->method('getId')->willReturn(10);

        $recipient = $this->createMock(LeadRecipient::class);
        $recipient->method('getLead')->willReturn($lead);

        $leads = $this->createMock(LeadRepository::class);
        $leads->method('find')->with(10)->willReturn($lead);

        $recipients = $this->createMock(LeadRecipientRepository::class);
        $recipients->method('find')->with(20)->willReturn($recipient);

        $validator = new LeadReleaseValidator($signer, $leads, $recipients);

        $now = 1_700_000_000;
        $req = LeadReleaseRequest::fromRaw(
            uri: 'https://app.test/leads/release?lid=10&rid=20&exp=' . ($now + 60),
            lid: '10',
            rid: '20',
            exp: (string) ($now + 60),
            nowTs: $now,
        );

        $res = $validator->validate($req);

        self::assertTrue($res->isOk());
        self::assertSame(LeadReleaseValidationResult::OK, $res->getCode());
        self::assertSame($lead, $res->getLead());
        self::assertSame($recipient, $res->getRecipient());
    }
}

