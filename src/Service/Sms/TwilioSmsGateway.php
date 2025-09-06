<?php

namespace App\Service\Sms;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Placeholder Twilio gateway.
 * In dev environment, does not perform network calls; only logs.
 */
class TwilioSmsGateway implements SmsGatewayInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly KernelInterface $kernel,
        private readonly string $sid,
        private readonly string $token,
        private readonly string $from
    ) {
    }

    public function send(string $to, string $body): SmsResult
    {
        // Guard: in dev, do not perform network I/O; log and return ok
        if ($this->kernel->getEnvironment() === 'dev') {
            $this->logger->info('TwilioSmsGateway(dev): would send SMS; suppressed', [
                'from' => $this->from,
                'to' => $to,
                'length' => strlen($body),
            ]);
            return SmsResult::ok('dev-suppressed');
        }

        // Placeholder: no real Twilio client here yet. Just log parameters.
        // In production/staging, this should be replaced with the Twilio SDK call.
        $this->logger->info('TwilioSmsGateway: sending SMS (placeholder)', [
            'sid' => substr($this->sid, 0, 6) . '...',
            'from' => $this->from,
            'to' => $to,
            'length' => strlen($body),
        ]);

        // Since this is a placeholder, we do not actually send. Treat as success.
        return SmsResult::ok('twilio-placeholder');
    }
}

