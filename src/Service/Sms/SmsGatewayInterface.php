<?php

namespace App\Service\Sms;

interface SmsGatewayInterface
{
    public function send(string $to, string $body): SmsResult;
}

