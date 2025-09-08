<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service;
use App\Service\Lead\OutreachEmailFactory;
use PHPUnit\Framework\TestCase;

final class OutreachEmailFactoryTest extends TestCase
{
    public function testBuildLeadInviteEmail(): void
    {
        $city = new City('Denver');
        $service = (new Service())->setName('Mobile Dog Grooming');
        $lead = new Lead($city, $service, 'Jane Doe', 'jane@example.com');

        $factory = new OutreachEmailFactory('noreply@example.com', 'CleanWhiskers');
        $expiresAt = new \DateTimeImmutable('+1 hour');

        $email = $factory->buildLeadInviteEmail($lead, 'to@example.com', 'https://claim', 'https://unsub', $expiresAt);

        self::assertSame('noreply@example.com', $email->getFrom()[0]->getAddress());
        self::assertSame('to@example.com', $email->getTo()[0]->getAddress());
        self::assertStringContainsString('New Mobile Dog Grooming lead in Denver', (string) $email->getSubject());
        $text = (string) $email->getTextBody();
        self::assertStringContainsString('Claim this lead: https://claim', $text);
        self::assertStringContainsString('unsubscribe here: https://unsub', $text);
        self::assertStringContainsString('— CleanWhiskers', $text);
    }
}

