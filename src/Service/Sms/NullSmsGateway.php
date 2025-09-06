<?php

namespace App\Service\Sms;

use Psr\Log\LoggerInterface;

class NullSmsGateway implements SmsGatewayInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function send(string $to, string $body): SmsResult
    {
        // No-op: log and pretend it succeeded
        $this->logger->info('NullSmsGateway: SMS suppressed', [
            'to' => $to,
            'length' => strlen($body),
        ]);

        return SmsResult::ok('null');
    }
}

