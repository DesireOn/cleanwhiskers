<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service as GroomingService;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use App\Service\Lead\LeadClaimRequest;
use App\Service\Lead\LeadClaimValidationResult;
use App\Service\Lead\LeadClaimValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\UriSigner;

final class LeadClaimValidatorTest extends TestCase
{
    /** @var UriSigner&MockObject */
    private UriSigner $signer;
    /** @var LeadRepository&MockObject */
    private LeadRepository $leads;
    /** @var LeadRecipientRepository&MockObject */
    private LeadRecipientRepository $recipients;

    protected function setUp(): void
    {
        $this->signer = $this->createMock(UriSigner::class);
        $this->leads = $this->createMock(LeadRepository::class);
        $this->recipients = $this->createMock(LeadRecipientRepository::class);
    }

    public function testMissingParams(): void
    {
        $validator = new LeadClaimValidator($this->signer, $this->leads, $this->recipients);
        $req = new LeadClaimRequest('url', 'notdigit', '2', '', '', '0', 0);
        $res = $validator->validate($req);
        self::assertSame(LeadClaimValidationResult::MISSING_PARAMS, $res->getCode());
    }

    public function testNotFoundWhenReposReturnNullOrMismatch(): void
    {
        $validator = new LeadClaimValidator($this->signer, $this->leads, $this->recipients);
        $req = new LeadClaimRequest('url', '1', '2', 'a@b.com', 'tok', '0', 0);
        $this->leads->method('find')->with(1)->willReturn(null);
        $this->recipients->method('find')->with(2)->willReturn(null);
        $res = $validator->validate($req);
        self::assertSame(LeadClaimValidationResult::NOT_FOUND, $res->getCode());

        // Now return mismatched recipient lead
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');
        $lead = new Lead($city, $service, 'Owner', 'o@example.com');
        $otherLead = new Lead($city, $service, 'Owner2', 'o2@example.com');
        $this->leads->method('find')->willReturn($lead);
        $recipient = new LeadRecipient($otherLead, 'r@example.com', hash('sha256', 'tok'), new \DateTimeImmutable('+1 hour'));
        $this->recipients->method('find')->willReturn($recipient);
        $res2 = $validator->validate($req);
        self::assertSame(LeadClaimValidationResult::NOT_FOUND, $res2->getCode());
    }

    public function testInvalidSignatureAndAllowGuestReshow(): void
    {
        $now = 1_700_000_000;
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');
        $lead = new Lead($city, $service, 'Owner', 'o@example.com');
        $recipient = new LeadRecipient($lead, 'pro@example.com', hash('sha256', 'rawtok'), new \DateTimeImmutable('+2 hours'));
        $recipient->setClaimedAt(new \DateTimeImmutable('-5 minutes'));

        // Assign IDs and set expectations for repo lookups
        $this->setId($lead, 10);
        $this->setId($recipient, 5);
        $this->leads->method('find')->with(10)->willReturn($lead);
        $this->recipients->method('find')->with(5)->willReturn($recipient);
        $this->signer->method('check')->willReturn(false); // invalid signature

        $req = new LeadClaimRequest('url', '10', '5', 'Pro@Example.com', 'rawtok', (string) ($now + 3600), $now);

        $res = (new LeadClaimValidator($this->signer, $this->leads, $this->recipients))->validate($req);
        self::assertSame(LeadClaimValidationResult::ALLOW_GUEST_RESHOW, $res->getCode());
    }

    public function testExpiredExternalExp(): void
    {
        $now = 1_700_000_000;
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');
        $lead = new Lead($city, $service, 'Owner', 'o@example.com');
        $recipient = new LeadRecipient($lead, 'pro@example.com', hash('sha256', 'rawtok'), new \DateTimeImmutable('+2 hours'));

        $this->leads->method('find')->willReturn($lead);
        $this->recipients->method('find')->willReturn($recipient);
        $this->signer->method('check')->willReturn(true);

        $req = new LeadClaimRequest('url', '1', '2', 'pro@example.com', 'rawtok', (string) ($now - 10), $now);
        $res = (new LeadClaimValidator($this->signer, $this->leads, $this->recipients))->validate($req);
        self::assertSame(LeadClaimValidationResult::EXPIRED, $res->getCode());
    }

    public function testInvalidTokenOrEmail(): void
    {
        $now = 2_000_000;
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');
        $lead = new Lead($city, $service, 'Owner', 'o@example.com');
        $recipient = new LeadRecipient($lead, 'pro@example.com', hash('sha256', 'rawtok'), new \DateTimeImmutable('+5 minutes'));
        $this->leads->method('find')->willReturn($lead);
        $this->recipients->method('find')->willReturn($recipient);
        $this->signer->method('check')->willReturn(true);

        // Wrong token
        $reqWrong = new LeadClaimRequest('url', '1', '2', 'pro@example.com', 'WRONG', (string) ($now + 3600), $now);
        $resWrong = (new LeadClaimValidator($this->signer, $this->leads, $this->recipients))->validate($reqWrong);
        self::assertSame(LeadClaimValidationResult::INVALID_TOKEN_OR_EMAIL, $resWrong->getCode());
    }

    public function testOkWhenAllValid(): void
    {
        $now = time();
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');
        $lead = new Lead($city, $service, 'Owner', 'o@example.com');
        $recipient = new LeadRecipient($lead, 'pro@example.com', hash('sha256', 'rawtok'), new \DateTimeImmutable('+1 hour'));

        $this->leads->method('find')->willReturn($lead);
        $this->recipients->method('find')->willReturn($recipient);
        $this->signer->method('check')->willReturn(true);

        $req = new LeadClaimRequest('url', '1', '2', 'Pro@Example.com', 'rawtok', (string) ($now + 3600), $now);
        $res = (new LeadClaimValidator($this->signer, $this->leads, $this->recipients))->validate($req);
        self::assertSame(LeadClaimValidationResult::OK, $res->getCode());
        self::assertTrue($res->isOk());
    }

    private function setId(object $entity, int $id): void
    {
        $ref = new \ReflectionClass($entity);
        while (!$ref->hasProperty('id') && ($ref = $ref->getParentClass())) {}
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($entity, $id);
    }
}
