<?php

declare(strict_types=1);

namespace App\Tests\Double;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mime\Email;

/**
 * Test double that throws for non-alert recipients to simulate SMTP failures,
 * but allows alert emails (to the configured admin address) to be sent via the
 * inner mailer so they are captured by MailerAssertionsTrait.
 */
final class ConditionalFailingMailer implements MailerInterface
{
    public function __construct(
        private readonly MailerInterface $inner,
        private readonly string $alertsRecipient,
    ) {}

    public function send(RawMessage $message, ?\Symfony\Component\Mailer\Envelope $envelope = null): void
    {
        // Only allow the admin alert email through; fail all others
        if ($message instanceof Email) {
            $to = array_map(static fn($addr) => $addr->getAddress(), $message->getTo());
            if (in_array($this->alertsRecipient, $to, true)) {
                $this->inner->send($message, $envelope);
                return;
            }
        }

        throw new \RuntimeException('Simulated SMTP failure');
    }
}

