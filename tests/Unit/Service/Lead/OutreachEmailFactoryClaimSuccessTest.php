<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service as GroomingService;
use App\Service\Lead\OutreachEmailFactory;
use PHPUnit\Framework\TestCase;

final class OutreachEmailFactoryClaimSuccessTest extends TestCase
{
    public function testBuildLeadClaimSuccessEmailPlainText(): void
    {
        $city = new City('Testville');
        $service = new GroomingService();
        $service->setName('Grooming');
        $lead = new Lead($city, $service, 'Jane Customer', 'jane@example.com');
        $lead->setPhone('555-1111');
        $lead->setPetType('Dog');

        $factory = new OutreachEmailFactory('from@example.com', 'CleanWhiskers');

        $email = $factory->buildLeadClaimSuccessEmail($lead, 'pro@example.com', null, 'https://example.com/unsub');

        self::assertSame('from@example.com', $email->getFrom()[0]->getAddress());
        self::assertSame('pro@example.com', $email->getTo()[0]->getAddress());
        self::assertStringContainsString('You claimed the Grooming lead in Testville', $email->getSubject() ?? '');

        $text = $email->getTextBody();
        self::assertIsString($text);
        self::assertStringContainsString('Lead details', $text);
        self::assertStringContainsString('Jane Customer', $text);
        self::assertStringContainsString('jane@example.com', $text);
        self::assertStringContainsString('555-1111', $text);
        self::assertStringContainsString('Next steps', $text);
    }
}
