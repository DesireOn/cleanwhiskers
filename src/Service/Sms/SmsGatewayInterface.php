<?php

declare(strict_types=1);

namespace App\Service\Sms;

interface SmsGatewayInterface
{
    /**
     * Sends an SMS message.
     */
    public function send(string $toPhone, string $message): void;
}

