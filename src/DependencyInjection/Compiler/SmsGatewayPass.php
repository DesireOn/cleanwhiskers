<?php

namespace App\DependencyInjection\Compiler;

use App\Service\Sms\NullSmsGateway;
use App\Service\Sms\SmsGatewayInterface;
use App\Service\Sms\TwilioSmsGateway;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SmsGatewayPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('sms.provider')) {
            return;
        }

        $provider = strtolower((string) $container->getParameter('sms.provider'));
        $target = match ($provider) {
            'twilio' => TwilioSmsGateway::class,
            default => NullSmsGateway::class,
        };

        // Create/override alias so the interface resolves to the chosen implementation
        $container->setAlias(SmsGatewayInterface::class, $target)->setPublic(false);
    }
}

