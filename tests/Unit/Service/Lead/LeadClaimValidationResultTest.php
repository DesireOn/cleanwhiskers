<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service as GroomingService;
use App\Entity\LeadRecipient;
use App\Service\Lead\LeadClaimValidationResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class LeadClaimValidationResultTest extends TestCase
{
    public function testHttpStatusMapping(): void
    {
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');
        $lead = new Lead($city, $service, 'Owner', 'o@example.com');
        $recipient = new LeadRecipient($lead, 'pro@example.com', hash('sha256', 'tok'), new \DateTimeImmutable('+1 hour'));

        self::assertSame(Response::HTTP_OK, LeadClaimValidationResult::ok($lead, $recipient)->httpStatus());
        self::assertSame(Response::HTTP_OK, LeadClaimValidationResult::allowGuestReshow($lead, $recipient)->httpStatus());
        self::assertSame(Response::HTTP_NOT_FOUND, LeadClaimValidationResult::notFound()->httpStatus());
        self::assertSame(Response::HTTP_BAD_REQUEST, LeadClaimValidationResult::invalidSignature()->httpStatus());
    }
}
