<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service as GroomingService;
use App\Entity\LeadRecipient;
use App\Service\Lead\LeadReleaseValidationResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class LeadReleaseValidationResultTest extends TestCase
{
    public function testHttpStatusMapping(): void
    {
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');
        $lead = new Lead($city, $service, 'Owner', 'o@example.com');
        $recipient = new LeadRecipient($lead, 'pro@example.com', hash('sha256', 'tok'), new \DateTimeImmutable('+1 hour'));

        self::assertSame(Response::HTTP_OK, LeadReleaseValidationResult::ok($lead, $recipient)->httpStatus());
        self::assertSame(Response::HTTP_NOT_FOUND, LeadReleaseValidationResult::notFound()->httpStatus());
        self::assertSame(Response::HTTP_BAD_REQUEST, LeadReleaseValidationResult::invalidSignature()->httpStatus());
        self::assertSame(Response::HTTP_BAD_REQUEST, LeadReleaseValidationResult::expired()->httpStatus());
        self::assertSame(Response::HTTP_BAD_REQUEST, LeadReleaseValidationResult::missingParams()->httpStatus());
    }

    public function testFactoriesAndGetters(): void
    {
        $city = new City('Y');
        $service = (new GroomingService())->setName('Wash');
        $lead = new Lead($city, $service, 'Alice', 'alice@example.com');
        $recipient = new LeadRecipient($lead, 'pro2@example.com', hash('sha256', 'tok2'), new \DateTimeImmutable('+2 hours'));

        $ok = LeadReleaseValidationResult::ok($lead, $recipient);
        self::assertTrue($ok->isOk());
        self::assertSame(LeadReleaseValidationResult::OK, $ok->getCode());
        self::assertSame($lead, $ok->getLead());
        self::assertSame($recipient, $ok->getRecipient());

        $invalid = LeadReleaseValidationResult::invalidSignature();
        self::assertFalse($invalid->isOk());
        self::assertSame(LeadReleaseValidationResult::INVALID_SIGNATURE, $invalid->getCode());
        self::assertNull($invalid->getLead());
        self::assertNull($invalid->getRecipient());

        $expired = LeadReleaseValidationResult::expired();
        self::assertFalse($expired->isOk());
        self::assertSame(LeadReleaseValidationResult::EXPIRED, $expired->getCode());
        self::assertNull($expired->getLead());
        self::assertNull($expired->getRecipient());

        $missing = LeadReleaseValidationResult::missingParams();
        self::assertFalse($missing->isOk());
        self::assertSame(LeadReleaseValidationResult::MISSING_PARAMS, $missing->getCode());
        self::assertNull($missing->getLead());
        self::assertNull($missing->getRecipient());

        $nf = LeadReleaseValidationResult::notFound();
        self::assertFalse($nf->isOk());
        self::assertSame(LeadReleaseValidationResult::NOT_FOUND, $nf->getCode());
        self::assertNull($nf->getLead());
        self::assertNull($nf->getRecipient());
    }
}

