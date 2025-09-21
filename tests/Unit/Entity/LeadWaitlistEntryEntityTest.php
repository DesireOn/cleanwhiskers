<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\LeadWaitlistEntry;
use App\Entity\Service;
use PHPUnit\Framework\TestCase;

final class LeadWaitlistEntryEntityTest extends TestCase
{
    private function makeLead(): Lead
    {
        $city = new City('Testopolis');
        $service = (new Service())->setName('Mobile Dog Grooming');
        return new Lead($city, $service, 'Owner', 'owner@example.com');
    }

    public function testConstructWithAndWithoutRecipient(): void
    {
        $lead = $this->makeLead();
        $entry = new LeadWaitlistEntry($lead);
        self::assertSame($lead, $entry->getLead());
        self::assertNull($entry->getLeadRecipient());
        self::assertInstanceOf(\DateTimeImmutable::class, $entry->getCreatedAt());

        $recipient = new LeadRecipient($lead, 'groomer@example.com', 'hash', new \DateTimeImmutable('+1 day'));
        $entry2 = new LeadWaitlistEntry($lead, $recipient);
        self::assertSame($lead, $entry2->getLead());
        self::assertSame($recipient, $entry2->getLeadRecipient());

        $entry2->setLeadRecipient(null);
        self::assertNull($entry2->getLeadRecipient());
    }
}

