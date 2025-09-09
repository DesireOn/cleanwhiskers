<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mailer;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service;
use App\Service\Lead\OutreachEmailFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Mailer\MailerInterface;

final class OutreachEmailDeliveryTest extends KernelTestCase
{
    use MailerAssertionsTrait;

    private int $baselineCount = 0;

    protected function setUp(): void
    {
        self::bootKernel();
        // Record baseline mailer event count to assert deltas per test
        $this->baselineCount = \is_array($this->getMailerEvents()) ? \count($this->getMailerEvents()) : 0;
    }

    public function testLeadInviteEmailSendsAndRenders(): void
    {
        $container = static::getContainer();
        $mailer = $container->get(MailerInterface::class);
        $factory = $container->get(OutreachEmailFactory::class);

        $city = new City('Denver');
        $service = (new Service())->setName('Mobile Dog Grooming');
        $lead = new Lead($city, $service, 'Jane Doe', 'jane@example.com');

        $expiresAt = new \DateTimeImmutable('+1 hour');
        $email = $factory->buildLeadInviteEmail(
            $lead,
            'to@example.com',
            'https://app.local/claim',
            'https://app.local/unsub',
            $expiresAt,
        );

        $mailer->send($email);

        $events = $this->getMailerEvents();
        $new = \array_slice($events, $this->baselineCount);
        self::assertCount(1, $new, 'Exactly one new email should be sent');

        // Verify headers and body contain expected information
        $event = $new[0] ?? null;
        self::assertNotNull($event, 'Email event should be recorded');
        $sent = $event->getMessage();

        // Subject and recipients
        self::assertStringContainsString('New Mobile Dog Grooming lead in Denver', (string) $sent->getSubject());
        self::assertSame('to@example.com', $sent->getTo()[0]->getAddress());

        // Plain text body always present
        $text = (string) $sent->getTextBody();
        self::assertStringContainsString('Claim this lead: https://app.local/claim', $text);
        self::assertStringContainsString('unsubscribe here: https://app.local/unsub', $text);
    }

    public function testLeadAlreadyClaimedEmailSendsAndRenders(): void
    {
        $container = static::getContainer();
        $mailer = $container->get(MailerInterface::class);
        $factory = $container->get(OutreachEmailFactory::class);

        $city = new City('Austin');
        $service = (new Service())->setName('Cat Grooming');
        $lead = new Lead($city, $service, 'John Owner', 'owner@example.com');

        $email = $factory->buildLeadAlreadyClaimedEmail(
            $lead,
            'to-claimed@example.com',
            'https://app.local/unsub',
        );

        $mailer->send($email);

        $events = $this->getMailerEvents();
        $new = \array_slice($events, $this->baselineCount);
        self::assertCount(1, $new, 'Exactly one new email should be sent');

        $event = $new[0] ?? null;
        self::assertNotNull($event, 'Email event should be recorded');
        $sent = $event->getMessage();

        self::assertStringContainsString('Lead already claimed: Cat Grooming in Austin', (string) $sent->getSubject());
        self::assertSame('to-claimed@example.com', $sent->getTo()[0]->getAddress());

        $text = (string) $sent->getTextBody();
        self::assertStringContainsString('already been claimed', $text);
        self::assertStringContainsString('Unsubscribe: https://app.local/unsub', $text);
    }
}
