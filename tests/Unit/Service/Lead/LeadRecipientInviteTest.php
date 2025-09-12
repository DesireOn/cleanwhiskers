<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service as GroomingService;
use App\Service\Lead\LeadRecipientInvite;
use PHPUnit\Framework\TestCase;

final class LeadRecipientInviteTest extends TestCase
{
    public function testValueObjectCarriesRecipientAndRawToken(): void
    {
        $city = new City('Sofia');
        $service = (new GroomingService())->setName('Mobile Dog Grooming');
        $lead = new Lead($city, $service, 'Owner', 'owner@example.com');
        $recipient = new LeadRecipient($lead, 'pro@example.com', hash('sha256', 'tok'), new \DateTimeImmutable('+1 hour'));

        $invite = new LeadRecipientInvite($recipient, 'rawtok');
        self::assertSame($recipient, $invite->recipient);
        self::assertSame('rawtok', $invite->rawToken);
    }
}

