<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Console\Tester\CommandTester;

final class SendTestEmailCommandTest extends KernelTestCase
{
    use MailerAssertionsTrait;

    private int $baselineCount = 0;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->baselineCount = \is_array($this->getMailerEvents()) ? \count($this->getMailerEvents()) : 0;
    }

    public function testCommandSendsAnEmail(): void
    {
        $application = new Application(static::$kernel);

        $command = $application->find('app:mail:test');
        $tester = new CommandTester($command);
        $exit = $tester->execute([
            'to' => 'recipient@example.com',
            '--subject' => 'Test Delivery',
            '--text' => 'Body',
        ]);

        self::assertSame(0, $exit, 'Command should succeed');

        $events = $this->getMailerEvents();
        $new = \array_slice($events, $this->baselineCount);
        self::assertCount(1, $new, 'Exactly one new email should be sent');

        $event = $new[0] ?? null;
        self::assertNotNull($event);
        $sent = $event->getMessage();
        self::assertSame('recipient@example.com', $sent->getTo()[0]->getAddress());
        self::assertSame('Test Delivery', (string) $sent->getSubject());
    }
}
